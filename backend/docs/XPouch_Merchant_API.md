# XPouch Merchant API

Website-ready reference for the XPouch **Merchant API**.

## Table of Contents

- [Base URL](#base-url)
- [Authentication (required on all Merchant API calls)](#authentication-required-on-all-merchant-api-calls)
- [Standard Response Format](#standard-response-format)
- [A) Virtual Accounts](#a-virtual-accounts)
- [B) Utilities (VAS) for Merchants](#b-utilities-vas-for-merchants)
- [C) Transactions](#c-transactions)
- [D) Webhooks](#d-webhooks)
- [E) Security + Idempotency](#e-security--idempotency)

## Base URL

`https://backend.xpouch.co`

## Authentication (required on all Merchant API calls)

Send these headers on every request:

- `X-API-Key: <merchant_api_key>`
- `X-API-Secret: <merchant_api_secret>`
- `Accept: application/json`
- `Content-Type: application/json` (for `POST` requests)

## Standard Response Format

All responses follow this format:

- `success` (bool)
- `message` (string)
- `data` (object|array)
- `meta` (object; returned for paginated list endpoints)
- `errors` (object; returned for validation errors)

### Common Error Responses

Use these examples across endpoints.

#### 401 — Invalid credentials

```json
{
  "success": false,
  "message": "Invalid credentials",
  "data": null,
  "errors": {
    "auth": ["Invalid API key or secret"]
  }
}
```

#### 422 — Validation error

```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "field_name": ["The field_name field is required."]
  }
}
```

#### 400 — Provider failure

```json
{
  "success": false,
  "message": "Provider failure",
  "data": null,
  "errors": {
    "provider": ["Provider request failed. Please try again."]
  }
}
```

#### 500 — Server error

```json
{
  "success": false,
  "message": "Server error",
  "data": null,
  "errors": {
    "server": ["An unexpected error occurred."]
  }
}
```

---

## A) Virtual Accounts

### A1) Create customer virtual account

Creates a dedicated customer virtual account number for inbound payments.

- **Method + Path:** `POST /api/merchant/v1/virtual-accounts/create`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

#### Params

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `customer_reference` | string | Yes | Unique reference **per merchant** (idempotency key for account creation). | `cust_12345` |
| `customer_name` | string | Yes | Customer full name used for the account name formatting. | `John Doe` |

#### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/virtual-accounts/create" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_reference": "cust_12345",
    "customer_name": "John Doe"
  }'
```

#### Example success response

```json
{
  "success": true,
  "message": "Virtual account created successfully",
  "data": {
    "account_number": "6689417338",
    "account_name": "JOHN DOE (MERCHANT TRADING NAME)",
    "bank_name": "PalmPay",
    "currency_code": "NGN",
    "provider": "palmpay",
    "customer_reference": "cust_12345",
    "status": "active",
    "created_at": "2026-03-13T10:20:11.000000Z"
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### A2) List customer virtual accounts

- **Method + Path:** `GET /api/merchant/v1/virtual-accounts`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Params (query)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `status` | string | No | Filter by account status. | `active` |
| `customer_reference` | string | No | Filter by exact customer reference. | `cust_12345` |
| `per_page` | int | No | Page size. | `25` |

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/virtual-accounts?status=active&per_page=25" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

#### Example success response (paginated)

```json
{
  "success": true,
  "message": "Virtual accounts retrieved successfully",
  "data": [
    {
      "account_number": "6689417338",
      "account_name": "JOHN DOE (MERCHANT TRADING NAME)",
      "bank_name": "PalmPay",
      "currency_code": "NGN",
      "provider": "palmpay",
      "customer_reference": "cust_12345",
      "status": "active",
      "created_at": "2026-03-13T10:20:11.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 1,
    "last_page": 1
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### A3) Get a customer virtual account (and latest 10 transactions)

- **Method + Path:** `GET /api/merchant/v1/virtual-accounts/{customer_reference}`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Params (path)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `customer_reference` | string | Yes | The customer reference used when creating the virtual account. | `cust_12345` |

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/virtual-accounts/cust_12345" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

#### Example success response

```json
{
  "success": true,
  "message": "Virtual account retrieved successfully",
  "data": {
    "virtual_account": {
      "account_number": "6689417338",
      "account_name": "JOHN DOE (MERCHANT TRADING NAME)",
      "bank_name": "PalmPay",
      "currency_code": "NGN",
      "provider": "palmpay",
      "customer_reference": "cust_12345",
      "status": "active",
      "created_at": "2026-03-13T10:20:11.000000Z"
    },
    "latest_transactions": [
      {
        "reference": "MI2029888501608271872",
        "type": "credit",
        "category": "wallet_funding",
        "amount": 5000,
        "currency": "NGN",
        "status": "completed",
        "created_at": "2026-03-13T10:35:11.000000Z"
      }
    ]
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

## B) Utilities (VAS) for Merchants

Utilities include: **airtime**, **data**, **TV**, **electricity**, **education**.

### Endpoints (Utilities)

- `GET /api/merchant/v1/utilities/services`
- `GET /api/merchant/v1/utilities/services/{serviceId}/category`
- `GET /api/merchant/v1/utilities/services/{serviceId}/variations`
- `POST /api/merchant/v1/utilities/verify`
- `POST /api/merchant/v1/utilities/airtime`
- `POST /api/merchant/v1/utilities/data`
- `POST /api/merchant/v1/utilities/subscription`
- `POST /api/merchant/v1/utilities/electricity`
- `POST /api/merchant/v1/utilities/education`

### Quick start (recommended flow)

1. Fetch services: `GET /utilities/services?type=data` (or omit `type` to fetch all groups).
2. Fetch variations: `GET /utilities/services/{serviceId}/variations`
3. (Optional) verify account/meter: `POST /utilities/verify`
4. Purchase: `POST /utilities/<airtime|data|subscription|electricity|education>`

### Service discovery (render plans/services)

#### B1) List available services by category

- **Method + Path:** `GET /api/merchant/v1/utilities/services`

Returns service catalogs merchants can render on their website. If `type` is omitted, the response may include multiple groups (e.g. `airtime`, `data`, `tv`, `electricity`, `education`).

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

##### Params (query)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `type` | string | No | Filter by category: `airtime` \| `data` \| `tv` \| `electricity` \| `education`. | `data` |

##### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/utilities/services?type=data" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

##### Example success response

```json
{
  "success": true,
  "message": "Services retrieved successfully",
  "data": {
    "data": [
      {
        "id": 12,
        "service_id": "mtn-data",
        "name": "MTN Data",
        "minimum_amount": 50,
        "maximum_amount": 500000,
        "convenience_fee": 0,
        "product_type": "data",
        "image_url": "https://backend.xpouch.co/assets/services/mtn.png"
      }
    ],
    "airtime": [],
    "tv": [],
    "electricity": [],
    "education": []
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B2) Get service “category” info (if supported)

- **Method + Path:** `GET /api/merchant/v1/utilities/services/{serviceId}/category`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

##### Params (path)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `serviceId` | string | Yes | The service identifier from the services list. | `dstv` |

##### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/utilities/services/dstv/category" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

##### Example success response

```json
{
  "success": true,
  "message": "Service category retrieved successfully",
  "data": {
    "service_id": "dstv",
    "category": "tv"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B3) Get service variations/plans

- **Method + Path:** `GET /api/merchant/v1/utilities/services/{serviceId}/variations`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

##### Params (path)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `serviceId` | string | Yes | The service identifier from the services list. | `mtn-data` |

##### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/utilities/services/mtn-data/variations" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

##### Example success response

```json
{
  "success": true,
  "message": "Variations retrieved successfully",
  "data": [
    {
      "variation_code": "mtn_1gb_30d",
      "name": "1GB (30 Days)",
      "amount": 1000,
      "variation_amount": 1000,
      "currency": "NGN"
    }
  ]
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### Verification (smartcard/meter verification)

#### B4) Verify biller

- **Method + Path:** `POST /api/merchant/v1/utilities/verify`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `service_id` | string | Yes | Utility service identifier. | `dstv` |
| `billers_code` | string | Yes | Smartcard/IUC number or meter number (depends on service). | `1234567890` |

##### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/verify" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "service_id": "dstv",
    "billers_code": "1234567890"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "Verification successful",
  "data": {
    "service_id": "dstv",
    "billers_code": "1234567890",
    "customer_name": "JOHN DOE"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### Purchases

**Behavior:**

- Merchant wallet is charged.
- A transaction is recorded under `merchant_id`, and under `customer_reference` (if provided).
- On failure, XPouch refunds the merchant wallet and records a refund credit receipt.

**Transaction metadata behavior:**

- `metadata.user_request` (what merchant submitted)
- `metadata.provider_request` (what XPouch sent to provider)
- `metadata.provider_response` / `metadata.provider_error` (full provider payloads)

All purchase endpoints return a success payload like:

```json
{
  "success": true,
  "message": "... purchase successful",
  "data": {
    "transaction_id": 123,
    "reference": "202603131245....",
    "status": "completed"
  }
}
```

---

#### B5) Airtime purchase

- **Method + Path:** `POST /api/merchant/v1/utilities/airtime`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params (body)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `phone_number` | string | Yes | Recipient phone number. | `08012345678` |
| `amount` | number | Yes | Amount in NGN. | `500` |
| `service_id` | string | Conditionally | Required unless `network` is provided. | `airtel` |
| `network` | string | Conditionally | Required unless `service_id` is provided. One of `MTN`\|`Airtel`\|`Glo`\|`9mobile`. | `Airtel` |
| `customer_reference` | string | No | Your customer reference (stored as `merchant_customer_reference`). | `cust_12345` |

##### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/airtime" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "08012345678",
    "amount": 500,
    "network": "Airtel",
    "customer_reference": "cust_12345"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "Airtime purchase successful",
  "data": {
    "transaction_id": 123,
    "reference": "202603131245000001",
    "status": "completed",
    "service_type": "airtime",
    "service_id": "airtel",
    "phone_number": "08012345678",
    "amount": 500,
    "customer_reference": "cust_12345"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B6) Data purchase

- **Method + Path:** `POST /api/merchant/v1/utilities/data`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params (body)

Preferred:

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `phone_number` | string | Yes | Recipient phone number. | `08012345678` |
| `service_id` | string | Yes | Data service identifier. | `mtn-data` |
| `variation_code` | string | Yes | Plan code from variations endpoint. | `mtn_1gb_30d` |
| `customer_reference` | string | No | Your customer reference (stored as `merchant_customer_reference`). | `cust_12345` |

Legacy:

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `phone_number` | string | Yes | Recipient phone number. | `08012345678` |
| `data_plan_id` | int | Yes | Plan ID from `/utilities/services` list. | `12` |
| `customer_reference` | string | No | Your customer reference (stored as `merchant_customer_reference`). | `cust_12345` |

##### Example cURL (preferred)

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/data" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "08012345678",
    "service_id": "mtn-data",
    "variation_code": "mtn_1gb_30d",
    "customer_reference": "cust_12345"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "Data purchase successful",
  "data": {
    "transaction_id": 124,
    "reference": "202603131245000002",
    "status": "completed",
    "service_type": "data",
    "service_id": "mtn-data",
    "variation_code": "mtn_1gb_30d",
    "phone_number": "08012345678",
    "customer_reference": "cust_12345"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B7) TV subscription purchase

- **Method + Path:** `POST /api/merchant/v1/utilities/subscription`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params (body)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `service_id` | string | Yes | TV service identifier. | `dstv` |
| `amount` | number | Yes | Amount in NGN. | `9000` |
| `variation_code` | string | Yes | Bouquet/plan code from variations endpoint. | `dstv_premium` |
| `billers_code` | string | Yes | Smartcard/IUC number. | `1234567890` |
| `phone_number` | string | Yes | Customer phone number. | `08012345678` |
| `customer_reference` | string | No | Your customer reference. | `cust_12345` |

##### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/subscription" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "service_id": "dstv",
    "amount": 9000,
    "variation_code": "dstv_premium",
    "billers_code": "1234567890",
    "phone_number": "08012345678",
    "customer_reference": "cust_12345"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "TV subscription purchase successful",
  "data": {
    "transaction_id": 125,
    "reference": "202603131245000003",
    "status": "completed",
    "service_type": "tv",
    "service_id": "dstv",
    "variation_code": "dstv_premium",
    "billers_code": "1234567890",
    "phone_number": "08012345678",
    "amount": 9000,
    "customer_reference": "cust_12345"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B8) Electricity purchase

- **Method + Path:** `POST /api/merchant/v1/utilities/electricity`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params (body)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `service_id` | string | Yes | Electricity service identifier. | `ikeja-electric` |
| `amount` | number | Yes | Amount in NGN. | `5000` |
| `variation_code` | string | Yes | Variation from variations endpoint (disco/meter type). | `prepaid` |
| `meter_number` | string | Yes | Meter number. | `01234567890` |
| `phone_number` | string | Yes | Customer phone number. | `08012345678` |
| `meter_type` | string | No | `prepaid` \| `postpaid` (if supported). | `prepaid` |
| `customer_reference` | string | No | Your customer reference. | `cust_12345` |

##### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/electricity" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "service_id": "ikeja-electric",
    "amount": 5000,
    "variation_code": "prepaid",
    "meter_number": "01234567890",
    "phone_number": "08012345678",
    "meter_type": "prepaid",
    "customer_reference": "cust_12345"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "Electricity purchase successful",
  "data": {
    "transaction_id": 126,
    "reference": "202603131245000004",
    "status": "completed",
    "service_type": "electricity",
    "service_id": "ikeja-electric",
    "variation_code": "prepaid",
    "meter_number": "01234567890",
    "amount": 5000,
    "customer_reference": "cust_12345"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

#### B9) Education purchase

- **Method + Path:** `POST /api/merchant/v1/utilities/education`

##### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`
- `Content-Type: application/json`

##### Params (body)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `service_id` | string | Yes | Education service identifier. | `waec` |
| `variation_code` | string | Yes | Variation from variations endpoint (pin type). | `waec_result_checker` |
| `quantity` | int | Yes | Quantity to purchase. | `2` |
| `customer_reference` | string | No | Your customer reference. | `cust_12345` |

##### Example cURL

```bash
curl -X POST "https://backend.xpouch.co/api/merchant/v1/utilities/education" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "service_id": "waec",
    "variation_code": "waec_result_checker",
    "quantity": 2,
    "customer_reference": "cust_12345"
  }'
```

##### Example success response

```json
{
  "success": true,
  "message": "Education purchase successful",
  "data": {
    "transaction_id": 127,
    "reference": "202603131245000005",
    "status": "completed",
    "service_type": "education",
    "service_id": "waec",
    "variation_code": "waec_result_checker",
    "quantity": 2,
    "customer_reference": "cust_12345"
  }
}
```

##### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 400 provider failure: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

## C) Transactions

### C1) List + search merchant transactions

- **Method + Path:** `GET /api/merchant/v1/transactions`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Params (query)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `page` | int | No | Page number. | `1` |
| `per_page` | int | No | Page size. | `25` |
| `type` | string | No | `credit` \| `debit`. | `credit` |
| `category` | string | No | Category filter. | `wallet_funding` |
| `status` | string | No | `pending` \| `processing` \| `completed` \| `failed` \| `cancelled`. | `completed` |
| `currency_code` | string | No | Currency filter. | `NGN` |
| `customer_reference` | string | No | Maps to `merchant_customer_reference`. | `cust_12345` |
| `reference` | string | No | Exact match. | `202603131245000001` |
| `external_reference` | string | No | Exact match. | `ext_abc_123` |
| `q` | string | No | Search across `reference`, `external_reference`, `description`, `customer_reference`. | `cust_12345` |
| `start_date` | date | No | Start date (YYYY-MM-DD). | `2026-03-01` |
| `end_date` | date | No | End date (YYYY-MM-DD). | `2026-03-13` |

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/transactions?per_page=25&status=completed&customer_reference=cust_12345" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

#### Example success response (paginated)

```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": [
    {
      "id": 123,
      "type": "credit",
      "category": "wallet_funding",
      "amount": 5000,
      "fee": 0,
      "currency": "NGN",
      "reference": "MI2029888501608271872",
      "external_reference": null,
      "status": "completed",
      "description": "Virtual account funding",
      "merchant_customer_reference": "cust_12345",
      "created_at": "2026-03-13T10:35:11.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 1,
    "last_page": 1
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### C2) View a transaction

- **Method + Path:** `GET /api/merchant/v1/transactions/{reference}`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Params (path)

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `reference` | string | Yes | Transaction reference (exact). | `MI2029888501608271872` |

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/transactions/MI2029888501608271872" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

#### Example success response

```json
{
  "success": true,
  "message": "Transaction retrieved successfully",
  "data": {
    "id": 123,
    "type": "credit",
    "category": "wallet_funding",
    "amount": 5000,
    "fee": 0,
    "currency": "NGN",
    "reference": "MI2029888501608271872",
    "external_reference": null,
    "status": "completed",
    "description": "Virtual account funding",
    "merchant_customer_reference": "cust_12345",
    "created_at": "2026-03-13T10:35:11.000000Z"
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 422 validation: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

### C3) PalmPay-only merchant transactions

- **Method + Path:** `GET /api/merchant/v1/transactions/palmpay`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Params

Same as `GET /api/merchant/v1/transactions` (optional filtering/pagination).

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/transactions/palmpay?per_page=25" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>" \
  -H "Accept: application/json"
```

#### Example success response

```json
{
  "success": true,
  "message": "PalmPay transactions retrieved successfully",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 0,
    "last_page": 1
  }
}
```

#### Example error responses

- 401 invalid credentials: see [Common Error Responses](#common-error-responses)
- 500 server: see [Common Error Responses](#common-error-responses)

---

## D) Webhooks

Webhooks notify merchants of **payments received** and **utility purchase outcomes**.

### How to configure webhooks

- Configure `webhook_url` in your merchant profile.
  - If there is no API endpoint for it, set it in the XPouch dashboard (merchant settings).
- Your webhook endpoint must respond **HTTP 200 quickly**.
- Process events **idempotently** using `data.transaction_reference` (payments) or `data.reference` (utilities).

### Event: `payment_received`

#### Sample payload

```json
{
  "event": "payment_received",
  "data": {
    "transaction_reference": "MI2029888501608271872",
    "customer_reference": "cust_12345",
    "account_number": "6689417338",
    "amount": 5000,
    "currency": "NGN",
    "status": "completed",
    "paid_at": "2026-03-13T10:35:11.000000Z",
    "payer": {
      "account_number": "2067905391",
      "account_name": "MUSTAPHA ABUBAKAR",
      "bank_name": "KUDA"
    },
    "provider": "palmpay"
  }
}
```

### Event: `utility_success`

#### Sample payload

```json
{
  "event": "utility_success",
  "data": {
    "reference": "202603131245000003",
    "service_type": "tv",
    "customer_reference": "cust_12345",
    "status": "completed",
    "provider_response_summary": {
      "message": "Successful"
    }
  }
}
```

### Event: `utility_failed`

#### Sample payload

```json
{
  "event": "utility_failed",
  "data": {
    "reference": "202603131245000004",
    "service_type": "electricity",
    "customer_reference": "cust_12345",
    "status": "failed",
    "provider_response_summary": {
      "message": "Failed"
    }
  }
}
```

**To fetch full details** (including provider metadata), call:

- `GET /api/merchant/v1/transactions/{reference}`

---

## E) Security + Idempotency

### Recommendations

- Store raw webhook payloads (for audit and support investigations).
- Implement idempotency:
  - **Webhook events:** key by `data.transaction_reference`
  - **Utility purchases:** key by `data.reference` returned in purchase responses

### Verify signature

**TODO: signature verification**

If XPouch provides a webhook signature mechanism, validate it using the provider’s documented signature header (placeholder):

- `X-Webhook-Signature: <signature>`

Until signature verification is available, treat webhook endpoints as sensitive and restrict access (e.g., allowlist IPs if supported, rate-limit, and log all payloads).

---

## F) Merchant Balance

- **Method + Path:** `GET /api/merchant/v1/balance`

#### Required headers

- `X-API-Key`
- `X-API-Secret`
- `Accept: application/json`

#### Example cURL

```bash
curl -X GET "https://backend.xpouch.co/api/merchant/v1/balance" \
  -H "Accept: application/json" \
  -H "X-API-Key: <merchant_api_key>" \
  -H "X-API-Secret: <merchant_api_secret>"
```

#### Example success response

```json
{
  "success": true,
  "message": "Balance retrieved successfully",
  "data": {
    "merchant_id": 5,
    "business_name": "",
    "currency": "NGN",
    "available_balance": 0,
    "locked_balance": 0,
    "total_balance": 0
  }
}
```

RahmanData exposes both upstream provider balances through:

- `GET /api/v1/admin/settings/provider-balances`
