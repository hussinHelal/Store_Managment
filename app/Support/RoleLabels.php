<?php

namespace App\Support;

final class RoleLabels
{
    /**
     * @return array<string, string>
     */
    public static function map(): array
    {
        return [
            'Admin' => 'مدير',
            'Cashier' => 'كاشير',
            'Inventory Manager' => 'مدير المخزون',
            'Inventory manager' => 'مدير المخزون',
            'Staff viewer' => 'عرض الموظفين',
        ];
    }

    public static function arabic(string $name): string
    {
        return self::map()[$name] ?? $name;
    }
}
