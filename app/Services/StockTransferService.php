<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function __construct(private readonly StockService $stock) {}

    public function create(array $data, User $user, string $key): StockTransfer
    {
        return DB::transaction(function () use ($data, $user, $key): StockTransfer {
            $existing = StockTransfer::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                $sameItems = $existing->items()->orderBy('product_id')->get(['product_id', 'quantity'])->toArray()
                    === collect($data['items'])->map(fn ($item) => [
                        'product_id' => (int) $item['product_id'], 'quantity' => (int) $item['quantity'],
                    ])->sortBy('product_id')->values()->all();
                if ($sameItems && (int) $existing->from_branch_id === (int) $data['from_branch_id']
                    && (int) $existing->to_branch_id === (int) $data['to_branch_id']) {
                    return $existing->load('items');
                }
                throw ValidationException::withMessages(['idempotency_key' => 'مفتاح العملية مستخدم لطلب تحويل مختلف.']);
            }

            $this->validateBranches((int) $data['from_branch_id'], (int) $data['to_branch_id']);
            $transfer = StockTransfer::query()->create([
                'number' => 'TR-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(3))),
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'status' => 'draft',
                'created_by' => $user->id,
                'note' => $data['note'] ?? null,
                'idempotency_key' => $key,
            ]);
            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $transfer->load('items');
        }, 3);
    }

    public function updateDraft(StockTransfer $transfer, array $data): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $data): StockTransfer {
            $locked = StockTransfer::query()->lockForUpdate()->findOrFail($transfer->id);
            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages(['transfer' => 'لا يمكن تعديل تحويل بعد إرساله.']);
            }
            $this->validateBranches((int) $data['from_branch_id'], (int) $data['to_branch_id']);
            $locked->update([
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'note' => $data['note'] ?? null,
            ]);
            $locked->items()->delete();
            foreach ($data['items'] as $item) {
                $locked->items()->create(['product_id' => $item['product_id'], 'quantity' => $item['quantity']]);
            }

            return $locked->load('items');
        }, 3);
    }

    public function send(StockTransfer $transfer, User $user): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $user): StockTransfer {
            $locked = StockTransfer::query()->lockForUpdate()->with('items')->findOrFail($transfer->id);
            if ($locked->status !== 'draft' || $locked->items->isEmpty()) {
                throw ValidationException::withMessages(['transfer' => 'يجب أن يكون التحويل مسودة تحتوي على أصناف قبل الإرسال.']);
            }
            foreach ($locked->items->sortBy('product_id') as $item) {
                $this->stock->decrease(
                    (int) $item->product_id, (int) $item->quantity, 'transfer_out', $user,
                    'إرسال التحويل '.$locked->number, $locked, branchId: (int) $locked->from_branch_id,
                );
            }
            $locked->update(['status' => 'sent', 'sent_by' => $user->id, 'sent_at' => now()]);

            return $locked->refresh()->load('items');
        }, 3);
    }

    public function receive(StockTransfer $transfer, User $user): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $user): StockTransfer {
            $locked = StockTransfer::query()->lockForUpdate()->with('items')->findOrFail($transfer->id);
            if ($locked->status !== 'sent') {
                throw ValidationException::withMessages(['transfer' => 'لا يمكن استلام إلا تحويل تم إرساله.']);
            }
            foreach ($locked->items->sortBy('product_id') as $item) {
                $this->stock->increase(
                    (int) $item->product_id, (int) $item->quantity, 'transfer_in', $user,
                    'استلام التحويل '.$locked->number, $locked, branchId: (int) $locked->to_branch_id,
                );
            }
            $locked->update(['status' => 'received', 'received_by' => $user->id, 'received_at' => now()]);

            return $locked->refresh()->load('items');
        }, 3);
    }

    public function cancel(StockTransfer $transfer, string $reason, User $user): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $reason, $user): StockTransfer {
            $locked = StockTransfer::query()->lockForUpdate()->with('items')->findOrFail($transfer->id);
            if (! in_array($locked->status, ['draft', 'sent'], true)) {
                throw ValidationException::withMessages(['transfer' => 'لا يمكن إلغاء تحويل مستلم أو ملغى.']);
            }
            if ($locked->status === 'sent') {
                foreach ($locked->items->sortBy('product_id') as $item) {
                    $this->stock->increase(
                        (int) $item->product_id, (int) $item->quantity, 'transfer_cancel', $user,
                        'تعويض إلغاء التحويل '.$locked->number.': '.$reason, $locked, branchId: (int) $locked->from_branch_id,
                    );
                }
            }
            $locked->update(['status' => 'cancelled', 'note' => trim($locked->note.' | إلغاء: '.$reason)]);

            return $locked->refresh()->load('items');
        }, 3);
    }

    public function deleteDraft(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            $locked = StockTransfer::query()->lockForUpdate()->findOrFail($transfer->id);
            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages(['transfer' => 'يمكن حذف المسودات فقط.']);
            }
            $locked->delete();
        }, 3);
    }

    private function validateBranches(int $from, int $to): void
    {
        if ($from === $to) {
            throw ValidationException::withMessages(['to_branch_id' => 'يجب اختيار فرع وجهة مختلف.']);
        }
        $valid = Branch::query()->whereIn('id', [$from, $to])->where('is_active', true)->count();
        if ($valid !== 2) {
            throw ValidationException::withMessages(['from_branch_id' => 'يجب أن يكون فرعا المصدر والوجهة نشطين.']);
        }
    }
}
