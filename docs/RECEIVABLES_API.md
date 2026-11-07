# Odoo receivables API

`GET /api/odoo/receivables.php` is a read-only, bearer-authenticated export API. It is additive: the existing `GET /api/odoo/payables.php` endpoint and its response contract are unchanged.

## Configuration

The payables and receivables endpoints are part of the same Odoo integration and use the same configuration variables:

```php
$ODOO_PAYABLES_API_TOKEN = 'replace-with-a-long-random-token';
$ODOO_PAYABLES_API_DATABASE = $DefaultDatabase;
$ODOO_PAYABLES_API_REQUIRE_HTTPS = true;
```

Do not define separate receivables credentials. Send the same `Authorization: Bearer <token>` used by the payables endpoint. Only `GET` is accepted. Responses use the same `success`, `generated_at`, `filters`, `pagination`, and `data` envelope as the payables API. Money and quantity fields are JSON strings to preserve decimal precision.

## Resources

Call the endpoint with one required `resource` value:

| Resource | Source tables | Purpose |
| --- | --- | --- |
| `customers` | `debtorsmaster`, `custbranch`, `paymentterms` | Customer master and branches |
| `items` | `stockmaster`, `stockcategory`, `taxcategories` | Product master and tax category, including discontinued products |
| `invoices` | `debtortrans` types 10/11, `debtortranstaxes`, `salesorders` | Invoices, credit notes, and authoritative header tax records |
| `invoice_lines` | `stockmoves`, `stockmovestaxes` | Historical products, quantities, prices, discounts, and taxes sold |
| `payments` | `debtortrans` type 12, `banktrans` | Customer receipts and unallocated balances |
| `payment_allocations` | `custallocns`, `debtortrans` | Receipt/credit-to-invoice links |

Example:

```text
GET /api/odoo/receivables.php?resource=invoice_lines&from_date=2026-01-01&to_date=2026-01-31&page=1&page_size=100
Authorization: Bearer <token>
```

Common filters are `customer_id`, `branch_id`, `from_date`, `to_date`, `updated_since`, `updated_until`, `page`, and `page_size` (maximum 500). Resource-specific filters include `invoice_id`, `item_id`/`stock_id`, invoice `status`, and invoice `include_lines=true`.

Recommended extraction order: customers, items, invoices, invoice lines, payments, payment allocations.

## Curl examples

Set the endpoint and the shared Odoo integration token once:

```sh
export ZERP_RECEIVABLES_URL='https://zerp.example.com/api/odoo/receivables.php'
export ZERP_ODOO_API_TOKEN='replace-with-the-token-from-config.php'
```

The token is the value configured in `$ODOO_PAYABLES_API_TOKEN`. Do not use `curl --insecure` (`-k`) in production because it disables TLS certificate verification.

### Customers

Fetch the first page of customers and their branches:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=customers' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch one customer:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=customers' \
  --data-urlencode 'customer_id=CUSTOMER01' \
  "${ZERP_RECEIVABLES_URL}"
```

### Products/items

Fetch products, including active and discontinued items:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=items' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch one item by its webERP stock code:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=items' \
  --data-urlencode 'item_id=ITEM-001' \
  "${ZERP_RECEIVABLES_URL}"
```

`stock_id` is accepted as an alias for `item_id`.

### Invoices and credit notes

Fetch invoices and credit notes posted in a date range:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoices' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-01-31' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch open invoices for one customer and include their sold-item lines:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoices' \
  --data-urlencode 'customer_id=CUSTOMER01' \
  --data-urlencode 'status=open' \
  --data-urlencode 'include_lines=true' \
  "${ZERP_RECEIVABLES_URL}"
```

Supported invoice statuses are `open`, `partially_paid`, `paid`, and `voided`. The current schema has no debtor-header void flag, so a `voided` query returns no header records.

### Invoice lines/items sold

Fetch every posted invoice line within a date range:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoice_lines' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-01-31' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch the lines belonging to one invoice transaction ID:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoice_lines' \
  --data-urlencode 'invoice_id=12345' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch sales of one item for one customer, including hidden movements:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoice_lines' \
  --data-urlencode 'customer_id=CUSTOMER01' \
  --data-urlencode 'item_id=ITEM-001' \
  --data-urlencode 'include_voided=true' \
  "${ZERP_RECEIVABLES_URL}"
```

### Customer payments/receipts

Fetch customer receipts posted in a date range:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=payments' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-01-31' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=100' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch receipts for one customer and branch:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=payments' \
  --data-urlencode 'customer_id=CUSTOMER01' \
  --data-urlencode 'branch_id=MAIN' \
  "${ZERP_RECEIVABLES_URL}"
```

### Payment allocations

Fetch allocations made in a date range:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=payment_allocations' \
  --data-urlencode 'from_date=2026-01-01' \
  --data-urlencode 'to_date=2026-01-31' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_RECEIVABLES_URL}"
```

Fetch allocations targeting one invoice:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=payment_allocations' \
  --data-urlencode 'invoice_id=12345' \
  "${ZERP_RECEIVABLES_URL}"
```

### Incremental extraction

Use ISO 8601 timestamps with timezone offsets. The following example fetches invoice records posted during a closed synchronization window:

```sh
curl --fail-with-body --silent --show-error --get \
  --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
  --header 'Accept: application/json' \
  --data-urlencode 'resource=invoices' \
  --data-urlencode 'updated_since=2026-01-01T00:00:00+03:00' \
  --data-urlencode 'updated_until=2026-01-02T00:00:00+03:00' \
  --data-urlencode 'page=1' \
  --data-urlencode 'page_size=500' \
  "${ZERP_RECEIVABLES_URL}"
```

Review the incremental-sync limitations below before treating these timestamps as complete change-data capture.

### Paginating through all results

Read `pagination.has_next` from each response and increment `page` while it is `true`. This example requires `jq`:

```sh
page=1
while :; do
  response="$(curl --fail-with-body --silent --show-error --get \
    --header "Authorization: Bearer ${ZERP_ODOO_API_TOKEN}" \
    --header 'Accept: application/json' \
    --data-urlencode 'resource=customers' \
    --data-urlencode "page=${page}" \
    --data-urlencode 'page_size=500' \
    "${ZERP_RECEIVABLES_URL}")" || exit 1

  printf '%s\n' "${response}" | jq '.data[]'
  [ "$(printf '%s' "${response}" | jq -r '.pagination.has_next')" = 'true' ] || break
  page=$((page + 1))
done
```

To pretty-print any single response, append `| jq .` to its curl command.

## Accounting conventions

- Types 10 and 11 are customer invoices and credit notes; type 12 is a receipt.
- Posted `stockmoves` values are used for invoice lines, so later product renames or price changes do not replace historical quantities, prices, discounts, or narratives.
- Credit-note signs are returned exactly as stored by webERP. Consumers must not invert them a second time.
- Invoice totals use `ovamount + ovgst + ovfreight + ovdiscount`, matching webERP customer-balance logic.
- Every invoice and credit note includes a `taxes` array sourced from `debtortranstaxes`. Each record contains a stable `external_id`, tax-authority ID, description, and posted amount. The header `tax_amount` remains the authoritative total.
- Receipt amounts are exposed as positive receipt values; allocation amounts retain the value stored in `custallocns`.
- Line tax records include stable IDs in the form `zerp-stockmove-tax-<line_id>-<tax_authority_id>` and are calculated from the posted `stockmovestaxes` rates in calculation order. Header tax remains authoritative for document reconciliation because rounding is performed by webERP at posting time.
- `voided` is always false for debtor headers and allocations because this schema has no debtor-side void flag. The line `voided` value reflects `stockmoves.hidemovt`.

## Incremental-sync limitations

This webERP schema has no general `updated_at` fields on customer, item, allocation, or posted invoice-line records. Therefore:

- Invoice, line, and payment `updated_at` use the immutable posting `debtortrans.inputdate`.
- Customer creation uses `clientsince`; customers have no reliable modification timestamp.
- Item `updated_at` uses `lastcostupdate`, which tracks cost changes, not every master-data edit.
- Allocation creation uses `datealloc`, with day-level precision and no later modification timestamp.
- `updated_since`/`updated_until` filter those available source fields; they cannot guarantee change-data capture for later master-data edits or same-day allocation replacement.

For lossless synchronization, periodically re-extract a safety window and upsert by `external_id`. A future database migration should add maintained audit timestamps (or an append-only change log) to all six resources before consumers treat incremental extraction as complete CDC.

## Stable identifiers

Identifiers use these forms: `zerp-customer-<debtorno>`, `zerp-item-<stockid>`, `zerp-debtortrans-<id>`, `zerp-stockmove-<stkmoveno>`, and `zerp-custalloc-<id>`. Pagination ordering is deterministic within every resource.
