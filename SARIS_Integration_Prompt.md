You are a senior PHP developer. Build a complete PHP + MySQL web module called "SARIS Integration" that integrates with an external ERP REST API. Follow ALL specifications below exactly.

---

## 1. MAIN MENU & SUBMENUS

Add a top-level main menu item: **SARIS Integration** below the **Asset Manager** menu item
With four submenus:
- Settings
- Students
- Invoices
- Payments

---

## 2. DATABASE TABLES

Create the following MySQL tables (column names must exactly match the API response field names):

**`saris_settings`**
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
sync_mode ENUM('manual', 'automatic') NOT NULL DEFAULT 'manual',
sync_interval ENUM('10min', '30min', '1hr', '1day') NULL,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**`students`**
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
student_regnumber VARCHAR(100) UNIQUE,
student_fullname VARCHAR(255),
student_email VARCHAR(255),
student_phone VARCHAR(50),
student_programme VARCHAR(255),
student_entryyear VARCHAR(20),
student_studyyear INT,
student_intake VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**`invoices`**
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
student_name VARCHAR(255),
invoice_reference_number VARCHAR(100) UNIQUE,
student_regnumber VARCHAR(100),
invoice_amount DECIMAL(15,2),
invoice_amount_type VARCHAR(50),
invoice_desciption TEXT,
invoice_date DATETIME,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**`payments`**
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
student_name VARCHAR(255),
student_regnumber VARCHAR(100),
payment_desciption TEXT,
payment_amount DECIMAL(15,2),
payment_amount_type VARCHAR(50),
payment_currency VARCHAR(10),
payment_receipt_number VARCHAR(100),
payment_transaction_ref VARCHAR(100),
payment_date DATETIME,
payment_reference_number VARCHAR(100),
payment_source VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

---

## 3. ERP API CONFIGURATION

Base URL: `https://star.mum.ac.tz`

**Authentication:** POST `/api_erp/v1/auth`
- Body: `{ "client_id": "...", "client_secret": "..." }`
- Returns: `{ "token": "...", "expires_in": 900 }`
- Cache the token in a PHP session; re-authenticate when expired.

**Endpoints used:**
- GET `/api_erp/v1/get_all_invoices?invoice_date_from=YYYY-MM-DD&invoice_date_to=YYYY-MM-DD`
- GET `/api_erp/v1/get_student_info?reg_number=XXX`
- GET `/api_erp/v1/get_all_payments?payment_date_from=YYYY-MM-DD&payment_date_to=YYYY-MM-DD`

All requests must include: `Authorization: Bearer <token>` and `Content-Type: application/json`

---

## 4. SETTINGS SUBMENU

When the user clicks **Settings**, show a settings form with:
- A radio/select for **Sync Mode**: `Manual` or `Automatic`
- If `Automatic` is selected, show a dropdown for **Sync Interval**: `10 min`, `30 min`, `1 hr`, `1 day`
- A Save button that persists the settings to the `saris_settings` table (only one row; upsert)
- Below the form, display the current saved settings in a readable table

---

## 5. STUDENTS SUBMENU

**Default list view:** Display a paginated HTML data table of all records in the `students` table.

**If sync_mode = 'manual':** Show a dialog/modal form at the top or triggered by a "Sync Students" button with:
- **Start Date** (date picker): defaults to the MAX `invoice_date` from the `invoices` table (or today if table is empty). Format: `YYYY-MM-DD`
- **End Date** (date picker): must be >= Start Date
- **Submit** button

**On submit (manual) OR on automatic cron job:**
Run the full sync sequence in this exact order:

**Step 1 — Fetch Invoices:**
Call: `GET /api_erp/v1/get_all_invoices?invoice_date_from={start}&invoice_date_to={end}`
For each invoice record returned, INSERT or UPDATE into the `invoices` table using `invoice_reference_number` as the unique key.

**Step 2 — Fetch Students:**
For each unique `student_regnumber` found in the invoice results:
Call: `GET /api_erp/v1/get_student_info?reg_number={student_regnumber}`
INSERT or UPDATE into the `students` table using `student_regnumber` as the unique key. Skip if API returns 404.

**Step 3 — Fetch Payments:**
Using the same date range:
Call: `GET /api_erp/v1/get_all_payments?payment_date_from={start}&payment_date_to={end}`
INSERT or UPDATE into the `payments` table using `payment_receipt_number` (or composite key) as the unique key.

After completion, refresh the Students data table.

---

## 6. INVOICES SUBMENU

**Default list view:** Display a paginated HTML data table of all records in the `invoices` table, showing all columns.

No manual sync form needed here (invoices are synced via the Students submenu flow).

---

## 7. PAYMENTS SUBMENU

**Default list view:** Display a paginated HTML data table of all records in the `payments` table.

**If sync_mode = 'manual':** Show a dialog/modal or form triggered by a "Sync Payments" button with:
- **Start Date** (date picker): defaults to the MAX `payment_date` from the `payments` table (or today if empty). Format: `YYYY-MM-DD`
- **End Date** (date picker): must be >= Start Date
- **Submit** button

**On submit:**
Call: `GET /api_erp/v1/get_all_payments?payment_date_from={start}&payment_date_to={end}`
INSERT or UPDATE each record into the `payments` table.
After completion, refresh the Payments data table.

---

## 8. AUTOMATIC SYNC (CRON JOB)

Create a standalone PHP script `saris_cron.php` that:
1. Reads the `saris_settings` table; exits if `sync_mode != 'automatic'`
2. Determines the date range: from MAX `invoice_date` in `invoices` table to today
3. Runs the same full 3-step sync sequence as the manual Students sync
4. Logs results (success/errors) to a `saris_sync_log` table or a log file

Provide a crontab example for each interval option:
- 10 min: `*/10 * * * *`
- 30 min: `*/30 * * * *`
- 1 hr: `0 * * * *`
- 1 day: `0 2 * * *`

---

## 9. TECHNICAL REQUIREMENTS

- **Language:** PHP 7.4+ (use PDO for MySQL)
- **HTTP calls:** Use PHP cURL for all API requests
- **Token management:** Store token + expiry in PHP `$_SESSION`; auto-renew if expired before each API call
- **Error handling:** Show user-friendly error messages if API returns non-200 status
- **UI:** Use Bootstrap 5 for layout, modals, tables, and date pickers (use `<input type="date">`)
- **Security:** Sanitize all inputs; use prepared statements for all DB queries
- **File structure:**

while generating all files, adopt the ZERP File structure and programming skills and make sure there is  no placeholders. Include `db_setup.sql` with all CREATE TABLE statements.
