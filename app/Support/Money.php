<?php

namespace App\Support;

/**
 * All money arithmetic in the wallet module is done in integer cents.
 * Floats are only used to read and write 2-decimal values.
 */
final class Money
{
    public static function cents(mixed $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    /** 12345 -> "123.45" (for DECIMAL columns and form inputs). */
    public static function decimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    /** 1234567 -> "12,345.67" (for display). */
    public static function format(int $cents): string
    {
        return number_format($cents / 100, 2, '.', ',');
    }
}
