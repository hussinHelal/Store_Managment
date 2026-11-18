<?php

namespace App\Services;

use App\Models\products;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** The only application service allowed to mutate product/branch stock. */
class StockService
{
    private const MOVEMENT_TYPES = [
        'opening', 'purchase', 'sale', 'sale_edit', 'refund', 'invoice_void',
        'damage', 'damage_adjust', 'damage_void', 'transfer_out', 'transfer_in',
        'transfer_cancel', 'count_adjust', 'manual_adjust',
    ];

    public function __construct(private readonly CurrentBranch $currentBranch) {}

    public function increase(
        int $productId,
        int $quantity,
        string $type,
        ?User $actor = null,
        ?string $note = null,
        ?Model $reference = null,
        ?string $idempotencyKey = null,
        ?int $branchId = null,
    ): products {
        return $this->change($productId, $quantity, $type, $actor, $note, $reference, $idempotencyKey, $branchId);
    }

    public function decrease(
        int $productId,
        int $quantity,
        string $type,
        ?User $actor = null,
        ?string $note = null,
        ?Model $reference = null,
        ?string $idempotencyKey = null,
        ?int $branchId = null,
    ): products {
        return $this->change($productId, -$quantity, $type, $actor, $note, $reference, $idempotencyKey, $branchId);
    }

    public function change(
        int $productId,
        int $delta,
        string $type,
        ?User $actor = null,
        ?string $note = null,
        ?Model $reference = null,
        ?string $idempotencyKey = null,
        ?int $branchId = null,
    ): products {
        if ($delta === 0 || abs($delta) > 1_000_000 || ! in_array($type, self::MOVEMENT_TYPES, true)) {
            throw ValidationException::withMessages(['quantity' => 'يجب أن تكون حركة المخزون بين 1 و1,000,000 وحدة.']);
        }

        return DB::transaction(function () use ($productId, $delta, $type, $actor, $note, $reference, $idempotencyKey, $branchId): products {
            $product = products::query()->lockForUpdate()->findOrFail($productId);
            $branchId ??= $this->currentBranch->id($actor);
            if ($idempotencyKey) {
                $existing = DB::table('stock_movements')->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    if ((int) $existing->product_id !== $productId
                        || (int) $existing->branch_id !== $branchId
                        || (int) $existing->quantity_delta !== $delta
                        || $existing->type !== $type) {
                        throw ValidationException::withMessages(['idempotency_key' => 'مفتاح التكرار مستخدم لعملية مخزون مختلفة.']);
                    }

                    return $product;
                }
            }

            $this->initializeBranch($productId, $branchId);
            $branchStock = DB::table('branch_stock')->where('branch_id', $branchId)
                ->where('product_id', $productId)->lockForUpdate()->first();

            $before = (int) $branchStock->quantity;
            $after = $before + $delta;
            if ($after < 0) {
                throw ValidationException::withMessages(['quantity' => "المخزون غير كافٍ. المتاح في الفرع: {$before}."]);
            }
            if ($after > 1_000_000) {
                throw ValidationException::withMessages(['quantity' => 'تجاوزت الكمية الحد المسموح به للمخزون.']);
            }

            DB::table('branch_stock')->where('id', $branchStock->id)->update([
                'quantity' => $after,
                'updated_at' => now(),
            ]);
            $total = (int) DB::table('branch_stock')->where('product_id', $productId)->sum('quantity');
            $product->forceFill(['stock' => $total])->save();

            DB::table('stock_movements')->insert([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'type' => $type,
                'quantity_delta' => $delta,
                'balance_after' => $after,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'created_by' => $actor?->id,
                'note' => $note === null ? null : mb_substr(trim($note), 0, 500),
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $product->refresh();
        }, 3);
    }

    /** Set the legacy aggregate quantity while recording each branch-level movement. */
    public function setAggregate(
        int $productId,
        int $target,
        ?User $actor,
        string $reason,
        ?Model $reference = null,
        ?string $idempotencyKey = null,
    ): products {
        if ($target < 0 || $target > 1_000_000) {
            throw ValidationException::withMessages(['stock' => 'يجب أن يكون إجمالي المخزون بين صفر ومليون وحدة.']);
        }

        return DB::transaction(function () use ($productId, $target, $actor, $reason, $reference, $idempotencyKey): products {
            $product = products::query()->lockForUpdate()->findOrFail($productId);
            $delta = $target - (int) $product->stock;
            if ($delta === 0) {
                return $product;
            }

            $branchId = $this->currentBranch->id($actor);
            if ($delta > 0) {
                return $this->change($productId, $delta, 'manual_adjust', $actor, $reason, $reference,
                    $idempotencyKey ? hash('sha256', $idempotencyKey.':'.$branchId) : null, $branchId);
            }

            $remaining = -$delta;
            $this->initializeBranch($productId, $branchId);
            $balances = DB::table('branch_stock')->where('product_id', $productId)->orderBy('branch_id')->lockForUpdate()->get();
            $ordered = $balances->sortBy(fn ($row) => [(int) $row->branch_id === $branchId ? 0 : 1, (int) $row->branch_id]);
            foreach ($ordered as $balance) {
                if ($remaining === 0) {
                    break;
                }
                $take = min($remaining, (int) $balance->quantity);
                if ($take === 0) {
                    continue;
                }
                $key = $idempotencyKey ? hash('sha256', $idempotencyKey.':'.$balance->branch_id) : null;
                $this->change($productId, -$take, 'manual_adjust', $actor, $reason, $reference, $key, (int) $balance->branch_id);
                $remaining -= $take;
            }

            if ($remaining !== 0) {
                throw ValidationException::withMessages(['stock' => 'إجمالي المخزون المسجل غير متسق؛ لم يتم حفظ التعديل.']);
            }

            return $product->refresh();
        }, 3);
    }

    public function initializeBranch(int $productId, int $branchId): void
    {
        DB::transaction(function () use ($productId, $branchId): void {
            $product = products::query()->lockForUpdate()->findOrFail($productId);
            $exists = DB::table('branch_stock')->where('branch_id', $branchId)
                ->where('product_id', $productId)->lockForUpdate()->exists();
            if ($exists) {
                return;
            }

            $hasAnyBranchStock = DB::table('branch_stock')->where('product_id', $productId)->exists();
            $initialQuantity = $hasAnyBranchStock ? 0 : (int) $product->stock;
            DB::table('branch_stock')->insertOrIgnore([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'quantity' => $initialQuantity,
                'reorder_level' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($initialQuantity > 0) {
                DB::table('stock_movements')->insert([
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'type' => 'opening',
                    'quantity_delta' => $initialQuantity,
                    'balance_after' => $initialQuantity,
                    'note' => 'رصيد افتتاحي للسجل المنشأ بعد ترحيل مخزون الفروع',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }, 3);
    }

    /** @return array{products:int,branch_stock_mismatches:int,movement_mismatches:int} */
    public function verify(): array
    {
        $branchMismatch = DB::table('products as p')->leftJoin('branch_stock as bs', 'bs.product_id', '=', 'p.id')
            ->select('p.id')->groupBy('p.id', 'p.stock')->havingRaw('p.stock <> COALESCE(SUM(bs.quantity), 0)')->count();

        $lastMovement = [];
        $movementMismatch = 0;
        DB::table('stock_movements')->orderBy('branch_id')->orderBy('product_id')->orderBy('id')->cursor()
            ->each(function ($movement) use (&$lastMovement, &$movementMismatch): void {
                $key = $movement->branch_id.':'.$movement->product_id;
                $expected = ($lastMovement[$key] ?? 0) + (int) $movement->quantity_delta;
                if ($expected !== (int) $movement->balance_after) {
                    $movementMismatch++;
                }
                $lastMovement[$key] = (int) $movement->balance_after;
            });

        foreach ($lastMovement as $key => $balance) {
            [$branchId, $productId] = array_map('intval', explode(':', $key));
            $current = DB::table('branch_stock')->where('branch_id', $branchId)
                ->where('product_id', $productId)->value('quantity');
            if ((int) $current !== $balance) {
                $movementMismatch++;
            }
        }

        return [
            'products' => (int) DB::table('products')->count(),
            'branch_stock_mismatches' => $branchMismatch,
            'movement_mismatches' => $movementMismatch,
        ];
    }
}
