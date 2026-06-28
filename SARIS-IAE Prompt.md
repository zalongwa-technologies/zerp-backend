## ---

# SARIS-IAE API Integration Update — Developer Prompt

## Context

You are updating an existing PHP-based SARIS integration to work with the new **SARIS-IAE REST API (v1.0)**. The existing system likely uses direct database queries or an older SARIS interface. Your task is to refactor or update the integration layer to use the new API endpoints described below.

---

## New API Overview
- **Base URL:** `https://saris.iae.ac.tz` *(configurable — see API Configuration Settings below)*
- **Authentication:** OAuth2 Client Credentials (Bearer Token)
- **Token endpoint:** `POST /api/v1/login`
- **Token lifetime:** 3600 seconds (1 hour) — must be refreshed

---

## API Configuration Settings (Admin GUI)

### Overview

All API endpoint URLs must be **dynamically configurable** by a system administrator through a dedicated settings form — they must **not** be hardcoded in source files or config files that require developer intervention to change.

### Settings Form Location

Add a settings page titled **"SARIS API Configuration"** under the existing **SARIS Integration** menu. It should be accessible only to users with the **System Administrator** role.

### Form Fields

The form must include the following fields:

| Field Label | Field Name / Key | Description | Example Value |
|---|---|---|---|
| Base URL | `saris_base_url` | Root URL of the SARIS-IAE API server | `https://saris.iae.ac.tz` |
| Student Endpoint | `saris_student_endpoint` | Path for the students resource | `/api/v1/students` |
| Invoice Endpoint | `saris_invoice_endpoint` | Path for the invoices resource | `/api/v1/invoices` |
| Payment Endpoint | `saris_payment_endpoint` | Path for the payments resource | `/api/v1/payments` |
| Client ID | `saris_client_id` | OAuth2 client identifier | `CLIENT` |
| Client Secret | `saris_client_secret` | OAuth2 client secret (masked input) | `SECRET` |
| Token Endpoint | `saris_token_endpoint` | Path for token acquisition | `/api/v1/login` |

> **Note:** The full URL for each resource is constructed at runtime as: `{saris_base_url}{saris_student_endpoint}` (e.g. `https://saris.iae.ac.tz/api/v1/students`). Store the base URL and paths separately so that changing the base URL automatically applies to all endpoints.

### UI Requirements

- Each field should have a short inline help text or placeholder showing the expected format.
- The **Client Secret** field must use a password-type input (masked), with an optional "show/hide" toggle.
- Include a **"Save Settings"** button that persists the values to the database (not to a flat file).
- Include a **"Test Connection"** button that:
  - Uses the saved Base URL and token endpoint to attempt an OAuth2 token request.
  - Displays a success message (green) with the token expiry if the connection succeeds.
  - Displays a descriptive error message (red) if the connection fails (e.g. unreachable host, invalid credentials).
- After saving, display a confirmation message: *"SARIS API settings saved successfully."*
- Validate that all URL fields are non-empty and begin with `https://` before saving.

### Storage

- Store all settings in a dedicated database table named `saris_api_settings` (or equivalent system settings table if one already exists).
- Suggested schema:

```sql
CREATE TABLE IF NOT EXISTS saris_api_settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

- Provide a helper function/method `getSarisSetting($key)` that retrieves a value by key, with an optional default fallback.

### Integration with the API Client

The API client class must read all URLs from the settings table at runtime, not from hardcoded constants or `.env` files. Example:

```php
$baseUrl      = $this->getSarisSetting('saris_base_url');
$studentPath  = $this->getSarisSetting('saris_student_endpoint', '/api/v1/students');
$invoicePath  = $this->getSarisSetting('saris_invoice_endpoint', '/api/v1/invoices');
$paymentPath  = $this->getSarisSetting('saris_payment_endpoint', '/api/v1/payments');
$tokenPath    = $this->getSarisSetting('saris_token_endpoint',   '/api/v1/login');
$clientId     = $this->getSarisSetting('saris_client_id');
$clientSecret = $this->getSarisSetting('saris_client_secret');

$studentUrl = $baseUrl . $studentPath;
```

---

## Authentication Implementation

Replace any existing authentication logic with an OAuth2 client_credentials flow. Credentials (`client_id`, `client_secret`) and the token endpoint URL must be read from the settings table (see above).

### Token Request
```
POST {saris_base_url}{saris_token_endpoint}
Content-Type: application/x-www-form-urlencoded

client_id=CLIENT&client_secret=SECRET&grant_type=client_credentials&scope=SARIS
```

### Successful Response

```json
{
  "success": true,
  "message": "Authentication successful",
  "results": {
    "access_token": "TOKEN_VALUE",
    "token_type": "Bearer",
    "expires_in": 3600,
    "scope": "SARIS"
  }
}
```

### Requirements

- Implement a **token cache** — store the token and its expiry time; only request a new token when expired or missing.
- All subsequent requests must include the header: `Authorization: Bearer <access_token>`
- On `INVALID_TOKEN` error response, automatically refresh the token and retry the request once.

---

## Endpoints to Integrate

### 1. GET /api/v1/students

Replaces any existing student lookup logic.

**Supported query parameters:**

| Parameter | Type | Notes |
|---|---|---|
| `regno` | string | Exact match |
| `entry_year` | string | Format: `2023/2024` |
| `name` | string | Exact match |
| `sex` | string | `M` or `F` |
| `campus` | string | Campus ID |
| `programme` | string | Programme code |
| `sponsor` | string | Sponsor name |
| `limit` | int | Records per page |
| `offset` | int | Pagination offset |
| `order` | string | `ASC` or `DESC` |

**Key response fields per student:**

- `RegNo`, `Name`, `Sex`, `ProgrammeofStudy`, `ProgrammeName`
- `Faculty`, `Campus`, `CampusName`, `EntryYear`
- `DBirth`, `Sponsor`, `Phone`, `Email`, `Nationality`, `MaritalStatus`
- `classes` array: each entry has `session`, `YearOfStudy`, `AYear`, `Semester`

---

### 2. GET /api/v1/invoices

Replaces any direct OAS invoice table queries.

**Supported query parameters:**

| Parameter | Type | Notes |
|---|---|---|
| `student_id` | string | Registration number |
| `control_number` | string | Exact match |
| `a_year` | string | Academic year |
| `status` | string | Invoice status |
| `fee_name` | string | Fee item name |
| `invoice_id` | string | Exact match |
| `date_from` | string | Format: `YYYY-MM-DD` |
| `date_to` | string | Format: `YYYY-MM-DD` |
| `limit` | int | Records per page |
| `offset` | int | Pagination offset |
| `order` | string | `ASC` or `DESC` |

---

### 3. GET /api/v1/payments

Replaces any direct payment table queries.

**Supported query parameters:**

| Parameter | Type | Notes |
|---|---|---|
| `student_id` | string | Registration number |
| `control_number` | string | |
| `a_year` | string | Academic year |
| `invoice_number` | string | |
| `fee_category` | string | |
| `date_from` | string | Format: `YYYY-MM-DD` |
| `date_to` | string | Format: `YYYY-MM-DD` |
| `include_invoice_details` | boolean | Default: `true`; set `false` when not needed |
| `limit` | int | Records per page |
| `offset` | int | Pagination offset |
| `order` | string | `ASC` or `DESC` |

---

## Standard Response Structure

Every endpoint returns:

```json
{
  "success": true | false,
  "message": "...",
  "results": {
    "count": 0,
    "data": []
  }
}
```

- Always check `success` before processing `results.data`
- `count` holds the total number of records returned

---

## Error Handling

Handle all error responses uniformly. The error structure is:

```json
{
  "success": false,
  "message": "Error description",
  "results": {
    "error_code": "ERROR_CODE"
  }
}
```

**Error codes to handle:**

| Code | Action |
|---|---|
| `INVALID_TOKEN` | Refresh token and retry the request once |
| `UNAUTHORIZED` | Log and alert — missing token in request |
| `INVALID_METHOD` | Fix the HTTP method used |
| `NO_DATA_FOUND` | Return empty result gracefully — not a fatal error |
| `INVALID_DATE` | Validate date format before sending the request |

---

## Implementation Requirements

1. **Create a reusable API client class/service** with methods:
   - `getToken()` — handles token fetch and caching
   - `get($endpoint, $params = [])` — authenticated GET with auto token refresh
   - `getStudents($filters = [])` — wraps `/students`
   - `getInvoices($filters = [])` — wraps `/invoices`
   - `getPayments($filters = [])` — wraps `/payments`
   - `getSarisSetting($key, $default = null)` — retrieves a value from `saris_api_settings`

2. **Token caching:** Store token + expiry in session, database, or file cache. Do not request a new token on every API call.

3. **Date validation:** Before passing `date_from` or `date_to`, validate they are in `YYYY-MM-DD` format.

4. **Pagination:** When fetching large datasets, use `limit` and `offset`. Default page size should be 50 unless otherwise required.

5. **Performance:** When payment invoice details are not needed by the caller, pass `include_invoice_details=false` to reduce payload size.

6. **No direct DB queries:** Remove any existing direct SQL queries to SARIS/OAS tables and replace them with calls to this API.

7. **Dynamic URLs:** All API URLs must be resolved from the `saris_api_settings` table at runtime. No URL or credential may be hardcoded in source code.

---

## Coding Standards

- Language: PHP (or match the existing codebase language)
- Use `curl` or an HTTP client library (e.g., Guzzle for PHP)
- Log all API errors with the endpoint, parameters, and `error_code` for debugging
- Throw or return meaningful exceptions/errors to the calling code
- Do not hardcode credentials — read `client_id` and `client_secret` from the `saris_api_settings` table

---

## Example: Settings-Driven Token + Student Lookup (PHP pseudocode)

```php
// Resolve URLs dynamically from settings
$baseUrl     = $this->getSarisSetting('saris_base_url');
$studentUrl  = $baseUrl . $this->getSarisSetting('saris_student_endpoint', '/api/v1/students');

// Fetch token (cached)
$token = $this->getToken();

// Call endpoint
$response = $this->get($studentUrl, [
    'regno' => 'IAE/BCS/001/2023'
]);

if ($response['success']) {
    $students = $response['results']['data'];
} else {
    $errorCode = $response['results']['error_code'];
    // handle error
}
```

---

## What NOT to Change

- Do not modify existing business logic that processes student/invoice/payment data — only update the data-fetching layer.
- Do not change the UI or display logic (other than adding the new API Configuration settings form under the SARIS Integration menu).
- Keep backward compatibility with how calling code currently receives results (adapt the API response shape to match the existing data format if needed).

---

## Deliverables

1. Updated or new API client class/service file(s)
2. Migration script to create the `saris_api_settings` table and seed default values
3. Admin settings form (view + controller) under the **SARIS Integration** menu, accessible to System Administrators only
4. Updated calls in any existing integration files (e.g., `SARISIntegration.php`, `saris_cron.php`, or equivalent)
5. Brief inline comments explaining where old code was replaced
6. A note on any breaking changes or fields that are missing compared to the old integration