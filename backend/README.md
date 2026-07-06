# RahmanData

Laravel API backend for RahmanData (authentication + wallet + Xpouch integration).

## Features

- **Auth**: Username/password registration + Sanctum token authentication
- **Password reset**: Username + phone number (no OTP)
- **Wallet**: Balance tracking and transactions
- **Xpouch**: Virtual account creation + webhook handling
- **Utilities (VAS)**: Airtime, data, TV, electricity, education (via Xpouch)
- **Data (ConnectData)**: Plan listing + data purchase
- **Transaction PIN**: Required for utility/data purchases

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Sanctum (API Authentication)

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd <project-folder>
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Setup database
```bash
php artisan migrate --seed
```

5. Start the server
```bash
php artisan serve
```

## API Documentation

- App docs index: `http://localhost:8000/docs`
- OpenAPI docs: `http://localhost:8000/docs/api`
- XPouch Merchant API reference: `http://localhost:8000/docs/xpouch-merchant-api`

## Environment Variables

Key environment variables to configure:

- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `KUDISMS_API_TOKEN` - For SMS/OTP services
- `XPOUCH_API_KEY`, `XPOUCH_API_SECRET`, `XPOUCH_WEBHOOK_SECRET` - For Xpouch
- `CONNECTDATA_TOKEN` - For ConnectData
- `APP_URL` - Your application URL
- `APP_NAME` - Application name (default: RahmanData)

## License

This project is private and proprietary.
