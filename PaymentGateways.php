<?php

declare(strict_types=1);

namespace App;

class CashPayment implements PaymentGatewayInterface
{
    public function charge(float $amount): bool
    {
        echo sprintf("[CASH] Received BDT %.2f in cash. Please give change if needed.\n", $amount);
        return true;
    }
}

class CardPayment implements PaymentGatewayInterface
{
    private string $cardNumberMasked;

    public function __construct(string $cardNumber = "4242424242424242")
    {
        $this->cardNumberMasked = str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
    }

    public function charge(float $amount): bool
    {
        echo sprintf("[CARD] Charging BDT %.2f to card %s ... Approved.\n", $amount, $this->cardNumberMasked);
        return true;
    }
}

class MobileBankingPayment implements PaymentGatewayInterface
{
    private string $provider;

    public function __construct(string $provider = "bKash")
    {
        $this->provider = $provider;
    }

    public function charge(float $amount): bool
    {
        echo sprintf("[MOBILE BANKING - %s] Sending payment request for BDT %.2f ... Confirmed.\n", $this->provider, $amount);
        return true;
    }
}