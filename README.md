# CashApp web-receipt Verifier

This package provides a simple utility to convert data from a CSV file into SQL queries for database insertion.

## Installation

To install the package, you can use [Composer](https://getcomposer.org/):

```bash
composer require ruhulfbr/cashapp-receipt-verifier
```

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Ruhulfbr\CashApp\WebReceiptVerifier;

$receipt = "your payment web receipt URL";  // (String) Required, CashApp Web receipt;
$username = "your_cash_app_username"; // (String) Required, CashApp Account Username;
$reference = "your_payment_reference"; // (String) Required, CashApp Payment Reference;

// With Named argument
// $cashApp = new WebReceiptVerifier(_USERNAME: $username, _REFERENCE: $reference);

// Together
$cashApp = new WebReceiptVerifier($username, $reference);
print_r($cashApp->verify($receipt));

```
## Response

```php
//Success
stdClass Object
(
    [type] => "success"
    [message] => "Web Receipt Verified Successfully."
    [query] => " stdClass Object";
)

//Error
stdClass Object
(
    [type] => "error"
    [message] => "CashApp payment `reference` is required"
)
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## 
This documentation provides clear instructions on the installation and usage of the package. It includes examples and explanations of each parameter, making it easy for users to understand how to use the package in their projects.