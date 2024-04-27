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