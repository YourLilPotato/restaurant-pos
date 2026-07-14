<?php

declare(strict_types=1);

namespace Tests;

use App\CheckoutTerminal;
use App\DeliveryOrder;
use App\DineInOrder;
use App\TakeawayOrder;
use App\PaymentGatewayInterface;
use PHPUnit\Framework\TestCase;

final class CheckoutTerminalTest extends TestCase
{
    public function testProcessCheckoutReturnsTrueWhenPaymentSucceeds(): void
    {
        $order = new DineInOrder();
        $order->addItem("Test Item", 100.00, 1);

        $payment = $this->createMock(PaymentGatewayInterface::class);
        $payment->expects($this->once())
            ->method('charge')
            ->with(115.00)
            ->willReturn(true);

        $terminal = new CheckoutTerminal();

        ob_start();
        $result = $terminal->processCheckout($order, $payment);
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testProcessCheckoutReturnsFalseWhenPaymentFails(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Test Item", 100.00, 1);

        $payment = $this->createMock(PaymentGatewayInterface::class);
        $payment->method('charge')->willReturn(false);

        $terminal = new CheckoutTerminal();

        ob_start();
        $result = $terminal->processCheckout($order, $payment);
        ob_end_clean();

        $this->assertFalse($result);
    }

    public function testProcessCheckoutPassesCorrectAmountForDeliveryOrder(): void
    {
        $order = new DeliveryOrder(60.00);
        $order->addItem("Item", 340.00, 1);

        $payment = $this->createMock(PaymentGatewayInterface::class);
        $payment->expects($this->once())
            ->method('charge')
            ->with($this->equalTo(417.00))
            ->willReturn(true);

        $terminal = new CheckoutTerminal();

        ob_start();
        $terminal->processCheckout($order, $payment);
        ob_end_clean();
    }

    public function testProcessCheckoutWorksPolymorphicallyAcrossOrderTypes(): void
    {
        $terminal = new CheckoutTerminal();

        $orders = [
            new DineInOrder(),
            new DeliveryOrder(),
            new TakeawayOrder(),
        ];

        foreach ($orders as $order) {
            $order->addItem("Generic Item", 50.00, 1);

            $payment = $this->createMock(PaymentGatewayInterface::class);
            $payment->method('charge')->willReturn(true);

            ob_start();
            $result = $terminal->processCheckout($order, $payment);
            ob_end_clean();

            $this->assertTrue($result, get_class($order) . " should process successfully.");
        }
    }

    public function testProcessCheckoutOutputsReceiptAndPaymentLog(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Item", 100.00, 1);

        $payment = $this->createMock(PaymentGatewayInterface::class);
        $payment->method('charge')->willReturn(true);

        ob_start();
        $terminal = new CheckoutTerminal();
        $terminal->processCheckout($order, $payment);
        $output = ob_get_clean();

        $this->assertStringContainsString("CHECKOUT START", $output);
        $this->assertStringContainsString("RESTAURANT POS", $output);
        $this->assertStringContainsString("Payment successful", $output);
        $this->assertStringContainsString("CHECKOUT END", $output);
    }
}
