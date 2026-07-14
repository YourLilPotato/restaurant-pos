<?php

declare(strict_types=1);

namespace App;

interface PaymentGatewayInterface
{
    public function charge(float $amount): bool;
}