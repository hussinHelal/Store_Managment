<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchService
{
    public function create(array $data): Branch
    {
        return DB::transaction(function () use ($data): Branch {
            $branch = Branch::query()->create($data + ['is_active' => true, 'is_main' => false]);
            DB::table('products')->orderBy('id')->chunk(500, function ($products) use ($branch): void {
                $now = now();
                $rows = $products->map(fn ($product) => [
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'reorder_level' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();
                if ($rows !== []) {
                    DB::table('branch_stock')->insert($rows);
                }
            });

            return $branch;
        }, 3);
    }

    public function update(Branch $branch, array $data): Branch
    {
        return DB::transaction(function () use ($branch, $data): Branch {
            $locked = Branch::query()->lockForUpdate()->findOrFail($branch->id);
            $deactivating = array_key_exists('is_active', $data) && ! $data['is_active'];
            if ($deactivating && ($locked->is_main || $this->hasStock($locked)
                || $locked->fromTransfers()->where('status', 'sent')->exists()
                || $locked->toTransfers()->where('status', 'sent')->exists())) {
                throw ValidationException::withMessages(['is_active' => 'أفرغ مخزون الفرع والتحويلات المعلقة قبل إيقافه.']);
            }
            unset($data['is_main']);
            $locked->update($data);

            return $locked->refresh();
        }, 3);
    }

    public function delete(Branch $branch): void
    {
        DB::transaction(function () use ($branch): void {
            $locked = Branch::query()->lockForUpdate()->findOrFail($branch->id);
            if ($locked->is_main || $this->hasStock($locked) || $locked->stockRows()->exists()
                || $locked->fromTransfers()->exists() || $locked->toTransfers()->exists()) {
                throw ValidationException::withMessages(['branch' => 'لا يمكن حذف الفرع الرئيسي أو فرع له مخزون أو سجل تحويلات.']);
            }
            $locked->delete();
        }, 3);
    }

    private function hasStock(Branch $branch): bool
    {
        return $branch->stockRows()->where('quantity', '>', 0)->exists();
    }
}
