<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class ProductCostSchema
{
    public static function available(): bool
    {
        return Schema::hasColumn('products', 'cost_price');
    }

    /** Keep catalog edits usable on databases that have not yet received the additive cost migration. */
    public static function prepare(array &$data, Request $request): void
    {
        if (self::available()) {
            return;
        }

        if ($request->filled('cost_price')) {
            throw ValidationException::withMessages([
                'cost_price' => 'يلزم تحديث قاعدة البيانات قبل حفظ تكلفة المنتج. لم يتم حفظ التغيير.',
            ]);
        }

        unset($data['cost_price']);
    }
}
