<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Order (Abstract Base Class)
 *
 * Encapsulates common order behaviour: managing line items, computing
 * subtotal/tax, and defining the contract (calculateFinalTotal) that
 * every concrete order type must implement in its own way.
 *
 * Encapsulation:
 *   $items is protected, never exposed directly. External code must go
 *   through addItem()/removeItem() to mutate it, and through the magic
 *   __get() to read derived values like subtotal/taxAmount/itemCount.
 *
 * Static Properties:
 *   TAX_RATE is a class constant shared and applied identically across
 *   every order type (DineIn, Delivery, Takeaway, ...).
 *
 * Magic Methods:
 *   __get() intercepts reads of undeclared/protected-style virtual
 *   properties (subtotal, taxAmount, itemCount) and computes them on
 *   the fly - so callers can do $order->subtotal but can never do
 *   $order->subtotal = 999 (no __set is defined, so writes fail silently
 *   or raise a notice, protecting data integrity).
 */
abstract class Order
{
    use ReceiptGenerator;

    /** Universal tax rate applied to every order type (5%). */
    public const TAX_RATE = 0.05;

    /**
     * @var array<int, array{name: string, price: float, qty: int}>
     */
    protected array $items = [];

    /**
     * Add an item to the order. This is the ONLY way to insert items,
     * keeping $items encapsulated and validated.
     */
    public function addItem(string $name, float $price, int $qty = 1): void
    {
        if ($price < 0 || $qty <= 0) {
            throw new InvalidArgumentException("Invalid item price/quantity for '{$name}'.");
        }

        $this->items[] = [
            'name'  => $name,
            'price' => $price,
            'qty'   => $qty,
        ];
    }

    /**
     * Remove an item by name (removes the first match found).
     */
    public function removeItem(string $name): bool
    {
        foreach ($this->items as $index => $item) {
            if ($item['name'] === $name) {
                unset($this->items[$index]);
                $this->items = array_values($this->items); // re-index
                return true;
            }
        }
        return false;
    }

    /**
     * Read-only accessor for the trait / external reporting code.
     * Still does not allow mutation of the underlying array - reference
     * misuse in a way that bypasses addItem/removeItem business rules,
     * since PHP arrays are copied by value when returned.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Abstraction: every concrete order type MUST define how its final
     * total is computed (service charge, delivery fee, takeaway discount...).
     */
    abstract public function calculateFinalTotal(): float;

    /**
     * Magic getter. Lets client code read computed/derived values as if
     * they were real properties (e.g. $order->subtotal), while keeping
     * $items itself protected and the computation centralised here.
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'subtotal':
                return array_reduce(
                    $this->items,
                    fn($carry, $item) => $carry + ($item['price'] * $item['qty']),
                    0.0
                );

            case 'taxAmount':
                return round($this->subtotal * static::TAX_RATE, 2);

            case 'itemCount':
                return array_reduce($this->items, fn($c, $i) => $c + $i['qty'], 0);

            default:
                trigger_error("Undefined or inaccessible property: {$name}", E_USER_NOTICE);
                return null;
        }
    }

    /**
     * No __set is implemented "silently": attempts to do
     * $order->subtotal = 500 will NOT overwrite anything because PHP
     * only calls __set() for inaccessible/undeclared properties, and
     * since we never store 'subtotal' as a real property, the write
     * is simply discarded (with a notice if __set were absent - here
     * we explicitly define it to make the intent clear and safe).
     */
    public function __set(string $name, $value): void
    {
        trigger_error("Cannot overwrite computed property '{$name}'. Use addItem()/removeItem() instead.", E_USER_WARNING);
    }
}