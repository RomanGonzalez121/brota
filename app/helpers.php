<?php

if (! function_exists('money')) {
    /**
     * Format an amount of Argentine pesos: 4500 → "$ 4.500".
     */
    function money(int|float $amount): string
    {
        return '$ '.number_format($amount, 0, ',', '.');
    }
}
