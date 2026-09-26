<?php

if (! function_exists('money')) {
    /**
     * Format an amount stored in cents.
     */
    function money(int $cents, string $currency = 'USD'): string
    {
        return ($currency === 'USD' ? '$' : $currency.' ').number_format($cents / 100, 2);
    }
}
