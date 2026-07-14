<?php

declare(strict_types=1);

namespace Tests;

use App\DeliveryOrder;
use App\DineInOrder;
use App\TakeawayOrder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testDineInOrderAppliesServiceChargeAndTax(): void
    {
        $order = new DineInOrder();
        $order->addItem("Chicken Biryani", 350.00, 2);
        $order->addItem("Borhani", 60.00, 2);
        $order->addItem("Firni", 90.00, 1);

        $this->assertSame(910.00, $order->subtotal);
        $this->assertSame(45.50, $order->taxAmount);
        $this->assertSame(1046.50, $order->calculateFinalTotal());
    }

    public function testDineInOrderWithNoItemsHasZeroTotal(): void
    {
        $order = new DineInOrder();
        $this->assertSame(0.0, $order->subtotal);
        $this->assertSame(0.0, $order->calculateFinalTotal());
    }
}