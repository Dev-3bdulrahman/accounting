# 📒 Accounting — Laravel ERP Module

[![Latest Version](https://img.shields.io/packagist/v/dev-3bdulrahman/accounting.svg?style=flat-square)](https://packagist.org/packages/dev-3bdulrahman/accounting)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%2B%20%7C%2012%2B-red?style=flat-square)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)

A full-featured **Accounting** module for Laravel ERP systems. Handles chart of accounts, journal entries, taxes, expense tracking, and bank account management — with full API and Livewire admin interface.

---

## Features

- Chart of Accounts (multi-level)
- Double-entry Journal Entries
- Tax Management (VAT / custom rates)
- Expense Tracking & Categorization
- Bank Account Management
- REST API endpoints
- Arabic & English translations

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 \| ^8.3 |
| Laravel | ^11.0 \| ^12.0 |

## Installation

```bash
composer require dev-3bdulrahman/accounting
```

Publish and run migrations:

```bash
php artisan vendor:publish --provider="Dev3bdulrahman\Accounting\Providers\AccountingServiceProvider"
php artisan migrate
```

## Service Provider

The package auto-discovers its service provider via Laravel's package discovery. If needed, register manually in `bootstrap/providers.php`:

```php
Dev3bdulrahman\Accounting\Providers\AccountingServiceProvider::class,
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## License

MIT License © [Abdulrahman](https://3bdulrahman.com)
