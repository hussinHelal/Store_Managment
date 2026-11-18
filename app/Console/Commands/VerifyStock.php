<?php

namespace App\Console\Commands;

use App\Services\StockService;
use Illuminate\Console\Command;

class VerifyStock extends Command
{
    protected $signature = 'stock:verify';

    protected $description = 'Report product totals, branch quantities, and movement-chain mismatches without changing data';

    public function handle(StockService $stock): int
    {
        $result = $stock->verify();
        $this->table(['الفحص', 'العدد'], [
            ['المنتجات المفحوصة', $result['products']],
            ['اختلاف إجمالي المنتج عن مجموع الفروع', $result['branch_stock_mismatches']],
            ['اختلاف رصيد حركة المخزون عن السجل', $result['movement_mismatches']],
        ]);

        return ($result['branch_stock_mismatches'] + $result['movement_mismatches']) === 0
            ? self::SUCCESS
            : self::FAILURE;
    }
}
