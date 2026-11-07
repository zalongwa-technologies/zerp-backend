# ZERP to Odoo Payables API

This read-only API exports supplier accounts-payable records from ZERP for import into Odoo Accounting. It includes suppliers, purchase invoices, debit notes, creditor payments, creditor journals, taxes, general-ledger entries, and payment allocations.

Each payable transaction includes a `taxes` array sourced from `supptranstaxes`, containing a stable `external_id`, tax-authority ID, description, and posted tax amount. Import these records together with the transaction; `tax_amount` is the authoritative transaction-level tax total. Tax IDs use `zerp-supptrans-tax-<transaction_id>-<tax_authority_id>`.

## Endpoint

```text
GET https://<zerp-host>/api/odoo/payables.php
```

The endpoint accepts only `GET` requests and returns JSON. It does not modify ZERP data.

## Server configuration

Add these settings to the ZERP runtime `config.php`. Do not commit the real token to source control.

```php
$ODOO_PAYABLES_API_TOKEN = 'replace-with-a-long-random-secret';

// Optional: defaults to $DefaultDatabase.
//$ODOO_PAYABLES_API_DATABASE = 'weberpdemo';

// Optional: true by default. Keep enabled in production.
//$ODOO_PAYABLES_API_REQUIRE_HTTPS = true;
```

Generate a strong token with `openssl rand -hex 32`. HTTPS is required by default. If TLS terminates at a reverse proxy, configure the web server so PHP receives the HTTPS state. Disable HTTPS enforcement only in isolated local development.

## Client authentication

Every request must supply the configured token:

```text
Authorization: Bearer <token>
```

Store the URL and token in environment variables for the curl examples:

```sh
export ZERP_PAYABLES_URL='https://zerp.example.com/api/odoo/payables.php'
export ZERP_ODOO_API_TOKEN='replace-with-the-token-from-config.php'
```

## Basic curl request

Fetch the first 100 payable transactions across all suppliers:

```sh
curl --fail-with-body --silent --show-error \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  "${ZERP_PAYABLES_URL}?page=1&page_size=100"
```

Pretty-print the response when `jq` is installed:

```sh
curl --fail-with-body --silent --show-error \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  "${ZERP_PAYABLES_URL}?page=1&page_size=100" | jq .
```

Do not use `curl --insecure` (`-k`) in production because it disables TLS certificate verification.

## Query parameters

| Parameter | Required | Default | Description |
|---|---:|---:|---|
| `supplier_id` | No | All suppliers | ZERP supplier code, up to 10 characters |
| `from_date` | No | No lower bound | Inclusive transaction date in `YYYY-MM-DD` format |
| `to_date` | No | No upper bound | Inclusive transaction date in `YYYY-MM-DD` format |
| `transaction_type` | No | `20,21,22,23` | Comma-separated transaction types |
| `include_void` | No | `false` | Include void transactions when `true` |
| `include_settled` | No | `true` | Include fully settled transactions when `true` |
| `page` | No | `1` | Positive page number |
| `page_size` | No | `100` | Records per page, from 1 to 500 |

Transaction types:

| Type | Meaning |
|---:|---|
| `20` | Purchase invoice/vendor bill |
| `21` | Supplier debit note/credit |
| `22` | Creditor payment |
| `23` | Creditor journal |

## Common curl examples

### Fetch one supplier's complete payable history

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'supplier_id=104824986' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_PAYABLES_URL}"
```

### Fetch one supplier within a date range

Both dates are inclusive.

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'supplier_id=104824986' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-11-05' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_PAYABLES_URL}"
```

### Fetch only supplier invoices

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --data-urlencode 'transaction_type=20' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-11-05' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_PAYABLES_URL}"
```

### Fetch invoices, debit notes, and payments

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --data-urlencode 'transaction_type=20,21,22' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_PAYABLES_URL}"
```

### Fetch only outstanding transactions

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --data-urlencode 'include_settled=false' \
  --data-urlencode 'include_void=false' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_PAYABLES_URL}"
```

### Save the response to a file

```sh
curl --fail-with-body --silent --show-error \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --output zerp-payables-page-1.json \
  "${ZERP_PAYABLES_URL}?from_date=2026-01-01&to_date=2026-11-05&page=1&page_size=500"
```

## Successful response

The API returns HTTP `200` for successful requests, including requests that find no records.

```json
{
  "success": true,
  "generated_at": "2026-11-05T14:30:00+03:00",
  "filters": {
    "supplier_id": "104824986",
    "from_date": "2026-01-01",
    "to_date": "2026-11-05",
    "transaction_types": [20, 21, 22, 23],
    "include_void": false,
    "include_settled": true
  },
  "pagination": {
    "page": 1,
    "page_size": 100,
    "total_records": 1,
    "total_pages": 1,
    "has_next": false
  },
  "data": [
    {
      "external_id": "zerp-supptrans-12345",
      "transaction_id": 12345,
      "transaction_number": 678,
      "transaction_type": 20,
      "transaction_type_name": "Purchase Invoice",
      "supplier": {
        "external_id": "zerp-supplier-104824986",
        "supplier_id": "104824986",
        "name": "Example Supplier",
        "currency": "TZS",
        "payment_terms": "30 Days",
        "tax_group_id": 1,
        "tax_reference": "TIN-123456",
        "address": {
          "line1": "Example Street",
          "line2": "Dar es Salaam",
          "line3": "",
          "line4": "",
          "line5": "",
          "line6": "Tanzania"
        },
        "contact": {
          "phone": "+255000000000",
          "email": "accounts@example.com",
          "people": []
        }
      },
      "supplier_reference": "INV-0012",
      "transaction_date": "2026-10-01",
      "due_date": "2026-10-31",
      "input_date": "2026-10-01T09:15:00+03:00",
      "currency": "TZS",
      "exchange_rate": 1.0,
      "net_amount": 100000.0,
      "tax_amount": 18000.0,
      "gross_amount": 118000.0,
      "allocated_amount": 50000.0,
      "outstanding_amount": 68000.0,
      "settled": false,
      "on_hold": false,
      "void": false,
      "narrative": "Office supplies",
      "cheque_number": "",
      "taxes": [
        {
          "tax_authority_id": 1,
          "description": "VAT",
          "amount": 18000.0
        }
      ],
      "accounting_lines": [],
      "allocations": [
        {
          "external_id": "zerp-suppalloc-456",
          "allocation_id": 456,
          "allocation_date": "2026-10-15",
          "allocated_amount": 50000.0,
          "source_transaction": {
            "external_id": "zerp-supptrans-12350",
            "transaction_id": 12350,
            "transaction_number": 700,
            "transaction_type": 22,
            "transaction_type_name": "Creditors Payment",
            "reference": "PAY-005"
          },
          "target_transaction": {
            "external_id": "zerp-supptrans-12345",
            "transaction_id": 12345,
            "transaction_number": 678,
            "transaction_type": 20,
            "transaction_type_name": "Purchase Invoice",
            "reference": "INV-0012"
          }
        }
      ]
    }
  ]
}
```

An empty query returns `"data": []`, `"total_records": 0`, `"total_pages": 0`, and `"has_next": false` with HTTP `200`.

## Pagination

Results are ordered by transaction input date and transaction ID. Read `pagination.has_next` after every response. If it is `true`, increment `page` while keeping every other filter unchanged.

Example Bash loop requiring `jq`:

```sh
page=1

while true; do
  response_file="zerp-payables-page-${page}.json"

  curl --fail-with-body --silent --show-error --get \
    --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
    --header 'Accept: application/json' \
    --data-urlencode 'from_date=2026-01-01' \
    --data-urlencode 'to_date=2026-11-05' \
    --data-urlencode "page=${page}" \
    --data-urlencode 'page_size=500' \
    --output "${response_file}" \
    "${ZERP_PAYABLES_URL}"

  jq -e '.success == true' "${response_file}" >/dev/null

  if [ "$(jq -r '.pagination.has_next' "${response_file}")" != 'true' ]; then
    break
  fi

  page=$((page + 1))
done
```

## Error responses

Errors use a consistent structure:

```json
{
  "success": false,
  "error": {
    "code": "INVALID_DATE",
    "message": "from_date must use YYYY-MM-DD format"
  }
}
```

| HTTP status | Meaning |
|---:|---|
| `400` | Invalid parameter, date, pagination value, or non-HTTPS request |
| `401` | Missing or incorrect bearer token |
| `404` | Requested supplier does not exist |
| `405` | A method other than `GET` was used |
| `500` | API configuration, database, or unexpected server error |

Because the examples use `--fail-with-body`, curl exits with a non-zero status for HTTP errors while preserving the JSON error body.

## Odoo synchronization guidance

Use these stable IDs when upserting records:

| ZERP entity | Stable external ID |
|---|---|
| Supplier | `zerp-supplier-{supplier_id}` |
| Transaction | `zerp-supptrans-{transaction_id}` |
| Allocation | `zerp-suppalloc-{allocation_id}` |

Recommended import sequence:

1. Request a bounded date range and process every page.
2. Upsert each supplier by `supplier.external_id`.
3. Upsert vendor bills, debit notes, journals, and payments by transaction `external_id`.
4. Import tax and accounting lines without changing their source signs.
5. After both source and target transactions exist in Odoo, reconcile each allocation by `external_id`.

Amount rules:

- `gross_amount = net_amount + tax_amount`.
- `outstanding_amount = gross_amount - allocated_amount`.
- Supplier payments are normally negative.
- GL and allocation signs are returned as stored by ZERP.
- Do not use `transaction_number` alone as a unique key; numbers can overlap between transaction types.

## Incremental synchronization limitation

`input_date` provides deterministic ordering but is not a complete change cursor. A later payment allocation can change an older invoice's allocated amount, outstanding amount, and settled status without changing its original input date.

Until ZERP has a durable integration outbox or change log:

1. Use overlapping transaction-date windows—for example, re-fetch the previous 30 days on every run.
2. Upsert suppliers, transactions, and allocations using their stable external IDs.
3. Treat allocations returned by the API as the current ZERP state.
4. Periodically run a wider reconciliation window to capture late payments and corrections.

## Source-data notes

- Taxes are joined through `supptranstaxes.supptransid`.
- GL entries match on both transaction type and transaction number.
- Allocations expose both `source_transaction` and `target_transaction`.
- Related records are batch-loaded after pagination to prevent duplicate transaction rows.
- The current `gltrans` schema has no tag column, so `accounting_lines[].tag` is `null`; `job_reference` is returned separately when present.
