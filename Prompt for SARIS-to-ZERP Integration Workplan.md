# SARIS-to-ZERP Integration Workplan

## Objective

Implement a reliable, idempotent synchronization pipeline that:

1. Pulls students, invoices, and payments from SARIS REST APIs into local staging tables.
2. Posts the staged records into ZERP/webERP through XML-RPC.
3. Preserves dependency order:

```text
Student → Customer → Customer Branch → Invoice → Receipt → Allocation
```

4. Records synchronization results so successfully posted records are not duplicated.

## Existing architecture

### SARIS REST API

SARIS data is currently imported using:

- `POST /api_erp/v1/auth`
- `GET /api_erp/v1/get_all_invoices`
- `GET /api_erp/v1/get_student_info`
- `GET /api_erp/v1/get_all_payments`

The client and import workflow are implemented in:

- `includes/SARISIntegration.php`
- `SARIS_Students.php`
- `SARIS_Payments.php`
- `saris_cron.php`

The local staging tables are:

- `students`
- `invoices`
- `payments`

### ZERP XML-RPC API

All XML-RPC methods use the transport endpoint configured by `$ZERP_EndPoint`:

```text
POST /api/api_xml-rpc.php
```

Required methods:

| Operation | XML-RPC method |
|---|---|
| Create customer | `weberp.xmlrpc_InsertCustomer` |
| Create customer branch | `weberp.xmlrpc_InsertBranch` |
| Create invoice | `weberp.xmlrpc_InsertSalesInvoice` |
| Create receipt | `weberp.xmlrpc_InsertDebtorReceipt` |
| Allocate receipt to invoice | `weberp.xmlrpc_AllocateTrans` |

Each method receives:

1. A method-specific associative-array payload.
2. ZERP username.
3. ZERP password.

Before implementation, inspect these files to confirm required fields, validation rules, and return formats:

- `api/includes/api_xml-rpc_definition.php`
- `api/includes/api_customers.php`
- `api/includes/api_branches.php`
- `api/includes/api_debtortransactions.php`

Do not implement against guessed method names such as `AddCustomer` or `AddCustomerPayment`.

## Phase 1: Confirm mappings and identifiers

Document the exact mappings before writing the posting logic.

### Student to customer

Determine mappings for at least:

- `student_regnumber` → `debtorno`
- `student_fullname` → `name`
- `student_email`
- `student_phone`
- Currency
- Payment terms
- Customer type
- Sales type

Use a deterministic customer code derived from the registration number. Validate it against the maximum length and allowed format of `debtorsmaster.debtorno`.

### Student to customer branch

Define a deterministic branch code, such as `MAIN`, for every student.

Confirm required values for:

- `debtorno`
- `branchcode`
- `brname`
- Address fields
- `phoneno`
- `email`
- Sales area
- Salesperson
- Default location
- Tax group
- Shipping method

### Invoice mapping

Confirm the required `InsertSalesInvoice` fields, including:

- Customer code
- Branch code
- Transaction date
- Customer/invoice reference
- Amount
- Sales area
- Part code
- Currency/rate where applicable

Assess the limitations of `InsertSalesInvoice`: it does not create complete stock, tax, sales-analysis, or cost-of-sales records. Document why this reduced invoice method is acceptable for student fee invoices, or select a more suitable accounting flow.

### Payment mapping

Confirm mappings for:

- Student/customer code
- Payment date
- Amount
- Currency or exchange rate
- Payment method
- Bank account
- Receipt reference

After creating the receipt, use its returned transaction number with `weberp.xmlrpc_AllocateTrans` to allocate it to the intended invoice.

Define how payments will match invoices. Do not assume every payment contains a valid invoice reference without verifying the SARIS data.

## Phase 2: Add synchronization metadata

Do not use only a single boolean `Status` field. A boolean cannot distinguish pending, partially completed, failed, or allocated records.

Add appropriate synchronization fields to `students`, `invoices`, and `payments`, for example:

```sql
sync_status VARCHAR(20) NOT NULL DEFAULT 'pending',
sync_attempts INT NOT NULL DEFAULT 0,
sync_error TEXT NULL,
synced_at DATETIME NULL
```

Add remote references where applicable:

```sql
-- students
zerp_customer_code VARCHAR(20) NULL,
customer_synced_at DATETIME NULL,
branch_synced_at DATETIME NULL;

-- invoices
zerp_invoice_no INT NULL;

-- payments
zerp_receipt_no INT NULL,
allocation_synced_at DATETIME NULL;
```

Recommended statuses:

- `pending`
- `processing`
- `partial`
- `synced`
- `failed`

Add unique indexes to the local source identifiers and references used for idempotency.

Provide database migration SQL compatible with the existing `db_setup.sql` and project upgrade mechanism.

## Phase 3: Build the XML-RPC client

Create a reusable client, for example:

```text
includes/ZERPXMLRPCClient.php
```

Responsibilities:

- Read configuration from `config.php`.
- Send XML-RPC requests to `$ZERP_EndPoint`.
- Apply username and password parameters correctly.
- Enforce connection and response timeouts.
- Decode XML-RPC responses and faults.
- Normalize success and error responses.
- Never log credentials or complete authentication payloads.

Use the project’s installed `phpxmlrpc/phpxmlrpc` package unless repository inspection proves another supported client is already standard.

Required client methods:

```php
insertCustomer(array $customer)
insertBranch(array $branch)
insertSalesInvoice(array $invoice)
insertDebtorReceipt(array $receipt)
allocateTransaction(array $allocation)
```

## Phase 4: Implement the posting service

Create:

```text
includes/ZERPSync.php
```

Expose:

```php
public function run(): array
```

The method must process records in this order.

### 1. Synchronize customers and branches

For each eligible student:

1. Derive the deterministic ZERP customer code.
2. Check whether the customer already exists in ZERP.
3. Create it only when it does not exist.
4. Check whether the default branch already exists.
5. Create the branch only when it does not exist.
6. Mark the student `synced` only after both customer and branch exist successfully.

If the customer succeeds but the branch fails, store a `partial` status. The next run must retry only the missing branch.

A “record already exists” response must be handled as an idempotent condition after confirming the existing record matches the intended identity.

### 2. Synchronize invoices

Only process invoices when:

- The linked student exists locally.
- The student’s customer and branch are synchronized.
- The invoice has not already been assigned a ZERP invoice number.

On success:

- Save the returned ZERP invoice number.
- Set `sync_status = 'synced'`.
- Clear the previous error.
- Set `synced_at`.

### 3. Synchronize payments

Only process payments when:

- The linked student/customer has been synchronized.
- The payment has not already been assigned a ZERP receipt number.

On successful receipt creation, persist the returned receipt number immediately.

### 4. Allocate payments

When a payment can be matched to a synchronized invoice:

1. Call `weberp.xmlrpc_AllocateTrans`.
2. Use the actual ZERP receipt and invoice references.
3. Record the allocation timestamp.

If receipt creation succeeds but allocation fails, mark the payment `partial`. The next run must retry the allocation without creating another receipt.

If no reliable invoice match exists, leave the receipt unallocated and record a clear reconciliation error.

## Phase 5: Transactions and concurrency

Prevent overlapping manual and cron executions.

Implement one of:

- MySQL advisory locking using `GET_LOCK()`.
- A database-backed synchronization lock.

For each record:

1. Atomically claim it by changing `pending` or `failed` to `processing`.
2. Perform the remote call.
3. Save the remote identifier immediately after success.
4. Update the final state.

Do not hold a database transaction open while waiting for an HTTP/XML-RPC response.

Use bounded retries. A failure must not result in an infinite retry loop.

## Phase 6: Integrate with the existing SARIS workflow

Invoke the ZERP posting service only after the SARIS pull has completed successfully.

Integrate it into `saris_full_sync()` or immediately after that function returns successfully. Ensure both manual synchronization and `saris_cron.php` use the same workflow.

The intended orchestration is:

```php
$sarisStats = saris_full_sync($sarisClient, $startDate, $endDate);

$zerpSync = new ZERPSync($zerpClient);
$zerpStats = $zerpSync->run();
```

A ZERP posting failure must not roll back successfully imported SARIS staging data.

Return separate statistics for:

- SARIS records imported.
- ZERP records posted.
- Records skipped.
- Partial records.
- Failed records.

## Phase 7: Logging

Use a dedicated database log table or the existing SARIS synchronization log. File logging may be added as a secondary output.

Every error entry must contain:

- Timestamp
- Run identifier
- Record type
- Local record ID
- External source reference
- XML-RPC method
- Attempt number
- Error or fault code
- Sanitized error message

Never log passwords, secrets, access tokens, or full payloads containing personal data.

Example:

```text
2026-06-18T14:30:22+03:00 run=abc123 type=invoice local_id=42
reference=INV-2026-0042 method=weberp.xmlrpc_InsertSalesInvoice
attempt=2 status=failed error="Customer branch MAIN was not found"
```

## Phase 8: Configuration

Retain the existing configuration variables where possible:

```php
$ZERP_EndPoint;
$ZERP_Username;
$ZERP_Password;
$ZERP_BankAccount;
$ZERP_SalesArea;
$ZERP_SalesPerson;
$ZERP_SalesType;
$ZERP_ShipVia;
```

Add any missing settings, such as:

```php
$ZERP_DefaultBranch = 'MAIN';
$ZERP_Currency = 'TZS';
$ZERP_PaymentMethod = 1;
$ZERP_DefaultLocation = '';
$ZERP_TaxGroup = 1;
$ZERP_XMLRPC_Timeout = 60;
```

Place examples in `config.distrib.php`, but keep real credentials only in the untracked runtime `config.php`.

## Verification requirements

Implement automated or reproducible tests covering:

1. A new student creates one customer and one branch.
2. Re-running synchronization does not duplicate them.
3. Branch failure produces a recoverable partial state.
4. An invoice waits until its customer and branch exist.
5. An invoice is not reposted after receiving a ZERP invoice number.
6. A receipt is created only once.
7. Allocation failure retries allocation without recreating the receipt.
8. XML-RPC faults are stored and reported.
9. Missing student or invoice relationships are skipped safely.
10. Concurrent runs cannot process the same record twice.
11. Manual and cron synchronization use the same service.
12. Credentials and tokens never appear in logs.

Validate all modified PHP files with `php -l`.

## Deliverables

Provide:

- Database migration SQL.
- `includes/ZERPXMLRPCClient.php`.
- `includes/ZERPSync.php`.
- Integration changes to the existing SARIS workflow and cron process.
- Configuration additions to `config.distrib.php`.
- Logging implementation.
- Tests or a repeatable verification script.
- A short README section documenting configuration, execution, retry behavior, and reconciliation.
- A final summary of files changed, tests run, assumptions, and unresolved data-mapping questions.

## Acceptance criteria

The integration is complete when:

- Records are posted in dependency order.
- Every successful ZERP transaction has its returned remote identifier stored locally.
- Re-running the process does not create duplicates.
- Partial customer/branch and receipt/allocation states recover safely.
- Failed records remain retryable with visible error details.
- Manual and automatic synchronization behave consistently.
- Existing SARIS staging imports continue working even when ZERP is unavailable.
- No credentials or sensitive payloads are exposed in source control or logs.

Do not begin implementation by guessing missing field mappings. Inspect the actual database schemas and XML-RPC validation functions first, document assumptions, and then implement the safest mapping supported by the repository.




===================================
Reset invoice and payment retries:
UPDATE invoices
SET sync_status = 'pending',
    sync_attempts = 0,
    sync_error = NULL
WHERE zerp_invoice_no IS NULL;

UPDATE payments
SET sync_status = CASE
        WHEN zerp_receipt_no IS NULL THEN 'pending'
        ELSE 'partial'
    END,
    sync_attempts = 0,
    sync_error = NULL
WHERE allocation_synced_at IS NULL;