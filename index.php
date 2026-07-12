<?php
declare(strict_types=1);

require_once __DIR__ . '/PaymentGatewayInterface.php';
require_once __DIR__ . '/PaymentGateways.php';
require_once __DIR__ . '/Order.php';
require_once __DIR__ . '/OrderTypes.php';
require_once __DIR__ . '/CheckoutTerminal.php';

$terminal = new CheckoutTerminal();

function captureOutput(callable $fn): string
{
    ob_start();
    $fn();
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Restaurant POS - Checkout</title>
</head>
<body>

<hr>
<h2>1. Dine-In Order - Cash Payment</h2>
<pre><?php
    $dineIn = new DineInOrder();
    $dineIn->addItem("Chicken Biryani", 350.00, 2);
    $dineIn->addItem("Borhani", 60.00, 2);
    $dineIn->addItem("Firni", 90.00, 1);

    $log = captureOutput(function () use ($terminal, $dineIn) {
        $terminal->processCheckout($dineIn, new CashPayment());
    });
    echo htmlspecialchars($log);
?></pre>

<hr>
<h2>2. Delivery Order - Card Payment</h2>
<pre><?php
    $delivery = new DeliveryOrder(60.00);
    $delivery->addItem("Beef Tehari", 280.00, 1);
    $delivery->addItem("Coke (500ml)", 60.00, 2);
    $delivery->removeItem("Coke (500ml)");
    $delivery->addItem("Coke (500ml)", 60.00, 1);

    $log = captureOutput(function () use ($terminal, $delivery) {
        $terminal->processCheckout($delivery, new CardPayment("5412987654323456"));
    });
    echo htmlspecialchars($log);
?></pre>

<hr>
<h2>3. Takeaway Order - Mobile Banking Payment</h2>
<pre><?php
    $takeaway = new TakeawayOrder();
    $takeaway->addItem("Chicken Shawarma", 180.00, 3);
    $takeaway->addItem("French Fries", 120.00, 1);

    $log = captureOutput(function () use ($terminal, $takeaway) {
        $terminal->processCheckout($takeaway, new MobileBankingPayment("bKash"));
    });
    echo htmlspecialchars($log);
?></pre>

<?php
$batch = [
    ['order' => new DineInOrder(),         'payment' => new CashPayment()],
    ['order' => new DeliveryOrder(50.00),  'payment' => new MobileBankingPayment("Nagad")],
    ['order' => new TakeawayOrder(),       'payment' => new CardPayment()],
];

foreach ($batch as $entry) {
    $entry['order']->addItem("Sample Item", 100.00, 1);
    $log = captureOutput(function () use ($terminal, $entry) {
        $terminal->processCheckout($entry['order'], $entry['payment']);
    });
    echo '<pre>' . htmlspecialchars($log) . '</pre>';
}
?>

</body>
</html>