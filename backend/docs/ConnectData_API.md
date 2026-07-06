# ConnectData (Upstream) — Data Plans API

This page documents the upstream ConnectData endpoints that RahmanData uses for **data plan listing** and **data purchases**.

## Base URL

`https://api.connectdata.ng`

## Authentication

ConnectData uses a token in the `Authorization` header.

Important: ConnectData requires the `Token` prefix:

- `Authorization: Token <token>`

In RahmanData:

- Data plans list uses `CONNECTDATA_PLANS_TOKEN` (fallbacks to `connectdata_token` if the env var is empty).
- Data purchase + balance use the token stored in admin settings (`connectdata_token`).

## 1) List merchant plans (dropdown source)

- **Method + Path:** `GET /api/network`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Token <token>`

### Example cURL

```bash
curl -X GET "https://api.connectdata.ng/api/network" \
  -H "Accept: application/json" \
  -H "Authorization: Token smm_qV9SN8KvKOrM7XauQ4Sg2Ss8L2GPdUudhWeCfXrF"
```

### RahmanData behavior

- Admin should set `connectdata_base_url` to the base URL only: `https://api.connectdata.ng` (do **not** include `/api`)
- `GET /api/v1/connectdata/data/plans` calls `GET {connectdata_base_url}/api/network`
- `POST /api/v1/connectdata/data/purchase` calls `POST {connectdata_base_url}/api/data`

### Example success response

```json
{
  "success": true,
  "data": {
    "MTN_PLAN": [
      {
        "id": 7,
        "dataplan_id": "7",
        "plan_type": "SME",
        "network": 1,
        "plan_network": "MTN",
        "month_validate": "--- 7 days",
        "plan": "500mb",
        "plan_amount": "350.00"
      }
    ],
    "AIRTEL_PLAN": [],
    "GLO_PLAN": [],
    "9MOBILE_PLAN": []
  }
}
```

## 2) Buy data

- **Method + Path:** `POST /api/data`
- **Headers:**
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Token <token>`

### Body params

| Name | Type | Required | Description | Example |
|---|---:|:---:|---|---|
| `plan` | string | Yes | Plan identifier (from the plans list). | `2` |
| `network` | int | Yes | Network identifier. | `1` |
| `mobile_number` | string | Yes | Recipient phone number. | `09037346247` |
| `Ported_number` | bool | Yes | Whether the number is ported. | `true` |
| `amount` | number | Yes | Amount. | `300` |

### Example cURL

```bash
curl -X POST "https://api.connectdata.ng/api/data" \
  -H "Accept: application/json" \
  -H "Authorization: Token <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "plan": "2",
    "network": 1,
    "mobile_number": "09037346247",
    "Ported_number": true,
    "amount": 300
  }'
```

### Example success response

```json
{
  "success": true,
  "message": "Transaction processed successfully",
  "data": {
    "id": 145,
    "reference": "BRG-7K4M1N9Q2P8R5T",
    "amount": "300.00",
    "total_debit": "303.00",
    "status": "success",
    "provider_reference": "Data_12345678900",
    "created_at": "2026-02-22T15:25:41.000000Z"
  }
}
```

## 3) Merchant transactions (admin verification)

If an admin wants to verify data transaction status from ConnectData:

- **Method + Path:** `GET /api/v1/merchant/transactions`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Token <token>`

```bash
curl -X GET "https://api.connectdata.ng/api/v1/merchant/transactions" \
  -H "Accept: application/json" \
  -H "Authorization: Token <token>"
```

## 4) Merchant balance

- **Method + Path:** `GET /api/v1/merchant/balance`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Token <token>`

### Example cURL

```bash
curl -X GET "https://api.connectdata.ng/api/v1/merchant/balance" \
  -H "Accept: application/json" \
  -H "Authorization: Token <token>"
```

### Notes

- This is the upstream route RahmanData uses to fetch the ConnectData merchant balance.
- RahmanData exposes this through `GET /api/v1/admin/settings/provider-balances`.
- Example upstream response:

```json
{
  "success": true,
  "balance": 0,
  "currency": "NGN"
}
```
