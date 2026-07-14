<?php

declare(strict_types=1);

namespace App;

/**
 * ReceiptGenerator (Trait)
 *
 * A trait lets us share a print-format method across any class that
 * needs it, without forcing an inheritance relationship. Order uses it
 * here, but a future "Invoice" or "KitchenTicket" class could reuse it too.
 */
trait ReceiptGenerator
{
    public function generatePrintFormat(): string
    {
        $lines = [];
        $lines[] = "================================";
        $lines[] = "        RESTAURANT POS         ";
        $lines[] = "================================";
        $lines[] = sprintf("Order Type: %s", static::class);
        $lines[] = "--------------------------------";

        foreach ($this->getItems() as $item) {
            $lineTotal = $item['price'] * $item['qty'];
            $lines[] = sprintf(
                "%-16s x%-3d %8.2f",
                $item['name'],
                $item['qty'],
                $lineTotal
            );
        }

        $lines[] = "--------------------------------";
        $lines[] = sprintf("%-20s %10.2f", "Subtotal:", $this->subtotal);

        // Extra charge line differs by order type; ask the subclass.
        if (method_exists($this, 'getExtraChargeLabel')) {
            $lines[] = sprintf("%-20s %10.2f", $this->getExtraChargeLabel() . ":", $this->getExtraChargeAmount());
        }

        $lines[] = sprintf("%-20s %10.2f", "Tax (" . (Order::TAX_RATE * 100) . "%):", $this->taxAmount);
        $lines[] = "================================";
        $lines[] = sprintf("%-20s %10.2f", "TOTAL:", $this->calculateFinalTotal());
        $lines[] = "================================";
        $lines[] = "     Thank you for dining!      ";
        $lines[] = "================================";

        $output = implode("\n", $lines) . "\n";
        echo $output;
        return $output;
    }
}