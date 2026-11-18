<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StocktakeService
{
    public function __construct(private readonly StockService $stock) {}

    public function start(array $data, User $user, string $key): Stocktake
    {
        return DB::transaction(function () use ($data, $user, $key): Stocktake {
            $existing = Stocktake::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                if ((int) $existing->branch_id === (int) $data['branch_id']
                    && $existing->scope === $data['scope']
                    && $existing->category_id == ($data['category_id'] ?? null)) {
                    return $existing->load('items.product');
                }
                throw ValidationException::withMessages(['idempotency_key' => 'مفتاح العملية مستخدم لجرد مختلف.']);
            }

            $branch = Branch::query()->whereKey($data['branch_id'])->where('is_active', true)->first();
            if (! $branch) {
                throw ValidationException::withMessages(['branch_id' => 'يجب اختيار فرع نشط.']);
            }
            $products = DB::table('products')->when(
                $data['scope'] === 'category',
                fn ($query) => $query->where('category_id', $data['category_id']),
            )->orderBy('id')->lockForUpdate()->get(['id']);
            foreach ($products as $product) {
                $this->stock->initializeBranch((int) $product->id, (int) $branch->id);
            }
            $snapshotAt = now();
            $snapshotMovementId = (int) (DB::table('stock_movements')->max('id') ?? 0);
            $branchBalances = DB::table('branch_stock')->where('branch_id', $branch->id)
                ->whereIn('product_id', $products->pluck('id'))->pluck('quantity', 'product_id');

            $stocktake = Stocktake::query()->create([
                'number' => 'ST-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(3))),
                'branch_id' => $branch->id,
                'scope' => $data['scope'],
                'category_id' => $data['scope'] === 'category' ? $data['category_id'] : null,
                'status' => 'counting',
                'started_by' => $user->id,
                'snapshot_at' => $snapshotAt,
                'snapshot_movement_id' => $snapshotMovementId,
                'note' => $data['note'] ?? null,
                'idempotency_key' => $key,
            ]);
            $rows = [];
            foreach ($products as $product) {
                $rows[] = [
                    'stocktake_id' => $stocktake->id,
                    'product_id' => $product->id,
                    'system_qty' => (int) ($branchBalances[$product->id] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                StocktakeItem::query()->insert($chunk);
            }

            return $stocktake->load('items.product');
        }, 3);
    }

    public function count(Stocktake $stocktake, int $itemId, int $counted, ?string $note = null): StocktakeItem
    {
        if ($counted < 0 || $counted > 1_000_000) {
            throw ValidationException::withMessages(['counted_qty' => 'يجب إدخال كمية بين 0 و1,000,000.']);
        }

        return DB::transaction(function () use ($stocktake, $itemId, $counted, $note): StocktakeItem {
            $lockedTake = Stocktake::query()->lockForUpdate()->findOrFail($stocktake->id);
            if (! in_array($lockedTake->status, ['counting', 'review'], true)) {
                throw ValidationException::withMessages(['stocktake' => 'يمكن إدخال العد أثناء العد أو المراجعة وقبل اعتماد الجرد فقط.']);
            }
            $item = StocktakeItem::query()->where('stocktake_id', $lockedTake->id)->whereKey($itemId)->firstOrFail();
            DB::table('products')->where('id', $item->product_id)->lockForUpdate()->first();
            $item->update([
                'counted_qty' => $counted,
                'counted_at' => now(),
                'counted_movement_id' => (int) (DB::table('stock_movements')->max('id') ?? 0),
                'note' => $note,
            ]);
            if ($lockedTake->status === 'review') {
                $item->update(['difference' => $this->differenceAtCount($lockedTake, $item)]);
            }

            return $item->refresh()->load('product');
        }, 3);
    }

    public function review(Stocktake $stocktake): Stocktake
    {
        return DB::transaction(function () use ($stocktake): Stocktake {
            $locked = Stocktake::query()->lockForUpdate()->findOrFail($stocktake->id);
            if ($locked->status !== 'counting' || $locked->items()->whereNull('counted_qty')->exists()) {
                throw ValidationException::withMessages(['stocktake' => 'أكمل عد جميع الأصناف قبل إرسال الجرد للمراجعة.']);
            }
            $items = $locked->items()->orderBy('product_id')->lockForUpdate()->get();
            foreach ($items as $item) {
                $item->update(['difference' => $this->differenceAtCount($locked, $item)]);
            }
            $locked->update(['status' => 'review']);

            return $locked->refresh();
        }, 3);
    }

    public function approve(Stocktake $stocktake, User $user): Stocktake
    {
        return DB::transaction(function () use ($stocktake, $user): Stocktake {
            $locked = Stocktake::query()->lockForUpdate()->with('items')->findOrFail($stocktake->id);
            if ($locked->status !== 'review' || $locked->items->contains(fn ($item) => $item->counted_qty === null)) {
                throw ValidationException::withMessages(['stocktake' => 'لا يمكن اعتماد الجرد قبل اكتمال المراجعة.']);
            }

            foreach ($locked->items->sortBy('product_id') as $item) {
                $difference = $this->differenceAtCount($locked, $item);
                $item->update(['difference' => $difference]);
                if ($difference !== 0) {
                    $this->stock->change(
                        (int) $item->product_id,
                        $difference,
                        'count_adjust',
                        $user,
                        'تسوية جرد '.$locked->number,
                        $locked,
                        branchId: (int) $locked->branch_id,
                    );
                }
            }

            $locked->update(['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()]);

            return $locked->refresh()->load('items.product');
        }, 3);
    }

    public function cancel(Stocktake $stocktake, string $reason): Stocktake
    {
        return DB::transaction(function () use ($stocktake, $reason): Stocktake {
            $locked = Stocktake::query()->lockForUpdate()->findOrFail($stocktake->id);
            if (! in_array($locked->status, ['counting', 'review'], true)) {
                throw ValidationException::withMessages(['stocktake' => 'لا يمكن إلغاء جرد معتمد أو ملغى.']);
            }
            $locked->update(['status' => 'cancelled', 'note' => trim($locked->note.' | إلغاء: '.$reason)]);

            return $locked->refresh();
        }, 3);
    }

    public function deleteDraft(Stocktake $stocktake): void
    {
        DB::transaction(function () use ($stocktake): void {
            $locked = Stocktake::query()->lockForUpdate()->findOrFail($stocktake->id);
            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages(['stocktake' => 'يمكن حذف المسودات فقط.']);
            }
            $locked->delete();
        }, 3);
    }

    private function differenceAtCount(Stocktake $stocktake, StocktakeItem $item): int
    {
        $countedMovementId = (int) ($item->counted_movement_id ?? $stocktake->snapshot_movement_id);
        $movementsSinceSnapshot = (int) DB::table('stock_movements')
            ->where('branch_id', $stocktake->branch_id)
            ->where('product_id', $item->product_id)
            ->where('id', '>', $stocktake->snapshot_movement_id)
            ->where('id', '<=', $countedMovementId)
            ->sum('quantity_delta');

        return (int) $item->counted_qty - ((int) $item->system_qty + $movementsSinceSnapshot);
    }
}
