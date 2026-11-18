<?php

namespace App\Support;

/**
 * Exact decimal parsing and integer-cent arithmetic shared by financial services.
 */
final class Money
{
    public static function cents(mixed $value): int
    {
        if (is_int($value)) {
            return $value * 100;
        }

        $decimal = trim((string) ($value ?? '0'));
        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/', $decimal, $matches)) {
            throw new \InvalidArgumentException('Money value must be a decimal number.');
        }

        $fraction = $matches[3] ?? '';
        $cents = ((int) $matches[2] * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
        if (isset($fraction[2]) && (int) $fraction[2] >= 5) {
            $cents++;
        }

        return ($matches[1] ?? '') === '-' ? -$cents : $cents;
    }

    /** 12345 -> "123.45" (for DECIMAL columns and form inputs). */
    public static function decimal(int $cents): string
    {
        $absolute = abs($cents);
        $decimal = intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return $cents < 0 ? '-'.$decimal : $decimal;
    }

    /** 1234567 -> "12,345.67" (for display). */
    public static function format(int $cents): string
    {
        $absolute = abs($cents);
        $decimal = number_format(intdiv($absolute, 100), 0, '.', ',').'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return $cents < 0 ? '-'.$decimal : $decimal;
    }
}
