<?php

declare(strict_types=1);

namespace App;

/**
 * DineInOrder
 *
 * Business rule: 10% service charge on the subtotal, PLUS the universal
 * tax rate defined in Order. Inheritance lets us reuse addItem,
 * removeItem, __get, and the ReceiptGenerator trait for free.
 */
class DineInOrder extends Order
{
    private const SERVICE_CHARGE_RATE = 0.10;

    public function calculateFinalTotal(): float
    {
        $subtotal = $this->subtotal; // via magic __get
        $serviceCharge = $subtotal * self::SERVICE_CHARGE_RATE;
        $tax = $subtotal * static::TAX_RATE; // universal rate from Order

        return round($subtotal + $serviceCharge + $tax, 2);
    }

    /** Used by ReceiptGenerator trait to label the extra charge line. */
    public function getExtraChargeLabel(): string
    {
        return "Service Charge (10%)";
    }

    public function getExtraChargeAmount(): float
    {
        return round($this->subtotal * self::SERVICE_CHARGE_RATE, 2);
    }
}

/**
 * DeliveryOrder
 *
 * Business rule: a flat delivery fee (not tied to order value), PLUS the
 * universal tax rate. Distance-based fee logic could be added later
 * without touching CheckoutTerminal or any other order type.
 */
class DeliveryOrder extends Order
{
    private float $deliveryFee;

    public function __construct(float $deliveryFee = 60.00)
    {
        $this->deliveryFee = $deliveryFee;
    }

    public function calculateFinalTotal(): float
    {
        $subtotal = $this->subtotal;
        $tax = $subtotal * static::TAX_RATE;

        return round($subtotal + $this->deliveryFee + $tax, 2);
    }

    public function getExtraChargeLabel(): string
    {
        return "Delivery Fee";
    }

    public function getExtraChargeAmount(): float
    {
        return round($this->deliveryFee, 2);
    }
}

/**
 * TakeawayOrder
 *
 * Business rule: no service charge, no delivery fee - just subtotal +
 * universal tax. Included to demonstrate the system scales cleanly to a
 * third order type with zero changes elsewhere (Open/Closed Principle).
 */
class TakeawayOrder extends Order
{
    public function calculateFinalTotal(): float
    {
        $subtotal = $this->subtotal;
        $tax = $subtotal * static::TAX_RATE;

        return round($subtotal + $tax, 2);
    }

    // No extra charge label/amount methods -> ReceiptGenerator trait
    // simply skips that line via method_exists() check.
}