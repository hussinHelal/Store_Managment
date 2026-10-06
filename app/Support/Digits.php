<?php

namespace App\Support;

final class Digits
{
    private const MAP = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٫' => '.', '٬' => '',
    ];

    /** Arabic-Indic digits and separators -> ASCII. */
    public static function ascii(?string $value): ?string
    {
        return $value === null ? null : strtr($value, self::MAP);
    }

    /**
     * Normalise a wallet number or InstaPay address typed by a person:
     * ASCII digits, no spaces/dashes in phone numbers, lowercase InstaPay address.
     */
    public static function identifier(?string $value): string
    {
        $value = trim((string) self::ascii($value));

        if (str_contains($value, '@')) {
            return mb_strtolower($value);
        }

        return (string) preg_replace('/[\s\-().]+/', '', $value);
    }

    public static function isEgyptianMobile(string $value): bool
    {
        return (bool) preg_match('/^01[0125]\d{8}$/', $value);
    }

    public static function isInstapayAddress(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9._-]{3,64}@[a-z]{2,20}$/', $value);
    }
}
