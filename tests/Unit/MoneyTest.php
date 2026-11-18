<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_decimal_money_uses_integer_cents_and_half_up_rounding(): void
    {
        self::assertSame(12345, Money::cents('123.45'));
        self::assertSame(12346, Money::cents('123.455'));
        self::assertSame(-12346, Money::cents('-123.455'));
        self::assertSame('123.45', Money::decimal(12345));
        self::assertSame('-123.45', Money::decimal(-12345));
    }

    public function test_invalid_money_notation_is_rejected_instead_of_silently_cast(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::cents('1e3');
    }
}
