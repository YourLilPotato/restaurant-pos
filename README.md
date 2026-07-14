# Restaurant POS

A vanilla PHP restaurant POS checkout system demonstrating core OOP principles:
- Interface
- Abstraction
- Inheritance
- Encapsulation
- Polymorphism
- Traits
- Static constants
- Magic methods

## Structure

```text
restaurant-pos/
├── composer.json
├── phpunit.xml
├── index.php
├── Order.php
├── OrderTypes.php
├── PaymentGatewayInterface.php
├── PaymentGateways.php
├── CheckoutTerminal.php
├── ReceiptGenerator.php
├── README.md
├── tests/
│   ├── OrderTest.php
│   ├── PaymentGatewayTest.php
│   ├── CheckoutTerminalTest.php
│   └── ReceiptGeneratorTest.php
└── vendor/
```

## Requirements

- PHP 8.0+
- Composer
- XAMPP (optional, for Apache/local serving)

## Install

```bash
composer install
```

## Run tests

```bash
composer test
```

## Run in browser

Put the project inside:

```text
C:\xampp\htdocs\restaurant-pos
```

Then start Apache from XAMPP and open:

```text
http://localhost/restaurant-pos/index.php
```

## Notes

- Autoloading is handled by Composer.
- Local class `require_once` statements were removed.
- `index.php` only loads `vendor/autoload.php`.
- `tests/` contains PHPUnit tests for orders, payment gateways, checkout flow, and receipt formatting.