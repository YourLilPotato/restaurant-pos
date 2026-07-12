<?php
interface PaymentGatewayInterface
{
        // ei interface er moddhe ekta function signature/method signature ache
    public function charge(float $amount): bool;
}
?>