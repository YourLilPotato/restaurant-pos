<?php

declare(strict_types=1);

namespace Tests;

use App\CardPayment;
use App\CashPayment;
use App\MobileBankingPayment;
use PHPUnit\Framework\TestCase;

final class PaymentGatewayTest extends TestCase
{
    public function testCashPaymentChargeReturnsTrue(): void
    {
        $payment = new CashPayment();
        $this->assertTrue($payment->charge(500.00));
    }

    public function testCashPaymentChargePrintsExpectedMessage(): void
    {
        $payment = new CashPayment();
        ob_start();
        $payment->charge(150.00);
        $output = ob_get_clean();

        $this->assertStringContainsString("[CASH]", $output);
        $this->assertStringContainsString("150.00", $output);
    }

    public function testCardPaymentChargeReturnsTrue(): void
    {
        $payment = new CardPayment("4242424242424242");
        $this->assertTrue($payment->charge(417.00));
    }

    public function testCardPaymentChargeOutputContainsMaskedCardNumber(): void
    {
        $payment = new CardPayment("5412987654323456");
        ob_start();
        $payment->charge(100.00);
        $output = ob_get_clean();

        $this->assertStringContainsString("************3456", $output);
    }

    public function testCardPaymentUsesDefaultCardWhenNoneProvided(): void
    {
        $payment = new CardPayment();
        ob_start();
        $payment->charge(50.00);
        $output = ob_get_clean();

        $this->assertStringContainsString("************4242", $output);
    }

    public function testMobileBankingPaymentChargeReturnsTrue(): void
    {
        $payment = new MobileBankingPayment("bKash");
        $this->assertTrue($payment->charge(693.00));
    }

    public function testMobileBankingPaymentUsesProvidedProvider(): void
    {
        $payment = new MobileBankingPayment("Nagad");
        ob_start();
        $payment->charge(200.00);
        $output = ob_get_clean();

        $this->assertStringContainsString("Nagad", $output);
        $this->assertStringContainsString("200.00", $output);
    }

    public function testMobileBankingPaymentDefaultsToBkash(): void
    {
        $payment = new MobileBankingPayment();
        ob_start();
        $payment->charge(100.00);
        $output = ob_get_clean();

        $this->assertStringContainsString("bKash", $output);
    }
}