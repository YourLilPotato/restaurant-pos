<?php

declare(strict_types=1);

namespace App;

/**
 * CheckoutTerminal
 *
 * Polymorphism in action: processCheckout() is typed against the
 * ABSTRACT Order class and the PaymentGatewayInterface - never against
 * a concrete class. This means:
 *   - No if ($order instanceof DineInOrder) ... else if (...) chains.
 *   - Adding a new order type or a new payment gateway requires ZERO
 *     changes to this class (Open/Closed Principle).
 */
class CheckoutTerminal
{
    /**
     * Process a checkout for ANY Order subtype using ANY payment gateway.
     */
    public function processCheckout(Order $order, PaymentGatewayInterface $payment): bool
    {
        $finalTotal = $order->calculateFinalTotal();

        echo "----- CHECKOUT START -----\n";
        $order->generatePrintFormat();

        $success = $payment->charge($finalTotal);

        echo $success
            ? "Payment successful. Order complete.\n"
            : "Payment FAILED. Order not completed.\n";
        echo "-----  CHECKOUT END  -----\n\n";

        return $success;
    }
}