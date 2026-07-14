<?php

declare(strict_types=1);

namespace Tests;

use App\DeliveryOrder;
use App\DineInOrder;
use App\TakeawayOrder;
use PHPUnit\Framework\TestCase;

final class ReceiptGeneratorTest extends TestCase
{
    public function testReceiptContainsHeaderAndFooter(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Sample Item", 100.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("RESTAURANT POS", $receipt);
        $this->assertStringContainsString("Thank you for dining!", $receipt);
    }

    public function testReceiptListsEachItemWithQuantityAndLineTotal(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Chicken Shawarma", 180.00, 3);
        $order->addItem("French Fries", 120.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("Chicken Shawarma", $receipt);
        $this->assertStringContainsString("540.00", $receipt);
        $this->assertStringContainsString("French Fries", $receipt);
        $this->assertStringContainsString("120.00", $receipt);
    }

    public function testReceiptShowsSubtotalTaxAndTotal(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Item", 100.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("Subtotal:", $receipt);
        $this->assertStringContainsString("100.00", $receipt);
        $this->assertStringContainsString("Tax (5%):", $receipt);
        $this->assertStringContainsString("5.00", $receipt);
        $this->assertStringContainsString("TOTAL:", $receipt);
        $this->assertStringContainsString("105.00", $receipt);
    }

    public function testReceiptIncludesServiceChargeLineForDineIn(): void
    {
        $order = new DineInOrder();
        $order->addItem("Item", 100.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("Service Charge (10%):", $receipt);
        $this->assertStringContainsString("10.00", $receipt);
    }

    public function testReceiptIncludesDeliveryFeeLineForDelivery(): void
    {
        $order = new DeliveryOrder(60.00);
        $order->addItem("Item", 100.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("Delivery Fee:", $receipt);
        $this->assertStringContainsString("60.00", $receipt);
    }

    public function testReceiptOmitsExtraChargeLineForTakeaway(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Item", 100.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringNotContainsString("Service Charge", $receipt);
        $this->assertStringNotContainsString("Delivery Fee", $receipt);
    }

    public function testReceiptDisplaysOrderTypeName(): void
    {
        $order = new DineInOrder();
        $order->addItem("Item", 50.00, 1);

        ob_start();
        $receipt = $order->generatePrintFormat();
        ob_end_clean();

        $this->assertStringContainsString("DineInOrder", $receipt);
    }

    public function testGeneratePrintFormatEchoesAndReturnsSameContent(): void
    {
        $order = new TakeawayOrder();
        $order->addItem("Item", 100.00, 1);

        ob_start();
        $returned = $order->generatePrintFormat();
        $echoed = ob_get_clean();

        $this->assertSame($echoed, $returned);
    }
}
