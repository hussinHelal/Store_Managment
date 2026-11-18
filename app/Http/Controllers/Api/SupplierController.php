<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateSupplierRequest;
use App\Http\Requests\Api\DeleteSupplierRequest;
use App\Http\Requests\Api\SupplierIndexRequest;
use App\Http\Requests\Api\UpdateSupplierRequest;
use App\Http\Requests\StorePurchaseReceiptRequest;
use App\Http\Requests\SupplierStatementRequest;
use App\Http\Resources\Api\PurchaseReceiptResource;
use App\Http\Resources\Api\SupplierResource;
use App\Models\products;
use App\Models\Supplier;
use App\Services\ApiIdempotencyService;
use App\Services\PurchaseReceivingService;
use App\Services\SupplierDeletionService;
use App\Services\SupplierStatementService;
use App\Support\Money;
use App\Support\ProductCostSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SupplierController
{
    public function receiptProducts(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $term = trim((string) ($filters['q'] ?? ''));
        $columns = ['id', 'name', 'barcode', 'stock'];
        if (ProductCostSchema::available()) {
            $columns[] = 'cost_price';
        }
        $query = products::query()->select($columns)
            ->when($term !== '', fn ($builder) => $builder->where(fn ($where) => $where
                ->where('name', 'like', '%'.addcslashes($term, '%_\\').'%')
                ->orWhere('barcode', 'like', '%'.addcslashes($term, '%_\\').'%')))
            ->orderBy('name');

        return response()->json($query->paginate($filters['per_page'] ?? 30)->through(fn (products $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'stock' => (int) $product->stock,
            'cost_price' => $product->cost_price === null ? null : Money::decimal(Money::cents($product->cost_price)),
        ]));
    }

    public function index(SupplierIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = Supplier::query()
            ->select(['id', 'name', 'phone', 'email', 'address', 'opening_balance', 'balance', 'notes'])
            ->withCount('purchases');
        $term = trim((string) ($filters['q'] ?? ''));

        if ($term !== '') {
            $search = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn ($query) => $query
                ->where('name', 'like', $search)
                ->orWhere('phone', 'like', $search)
                ->orWhere('email', 'like', $search));
        }

        return SupplierResource::collection(
            $query->orderByDesc('balance')->orderBy('name')->paginate($filters['per_page'] ?? 20)
        );
    }

    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier->loadCount('purchases'));
    }

    public function statement(SupplierStatementRequest $request, Supplier $supplier, SupplierStatementService $statements): JsonResponse
    {
        $filters = $request->validated();
        $statement = $statements->statement($supplier, $filters);
        $money = static fn (int $cents): string => Money::decimal($cents);

        return response()->json(['data' => [
            'supplier' => (new SupplierResource($supplier->loadCount('purchases')))->resolve($request),
            'summary' => [
                'opening_balance' => $money($statement['opening_balance']),
                'recorded_balance' => $money($statement['recorded_balance']),
                'unallocated_difference' => $money($statement['unallocated_difference']),
                'aging' => array_map($money, $statement['aging']),
            ],
            'purchases' => $statement['purchases']->through(fn ($purchase) => [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'total_amount' => Money::decimal(Money::cents($purchase->total_amount)),
                'paid_amount' => Money::decimal(Money::cents($purchase->paid_amount)),
                'remaining' => Money::decimal(Money::cents($purchase->remaining)),
                'status' => $purchase->status,
            ]),
            'payments' => $statement['payments']->through(fn ($payment) => [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'payment_date' => $payment->payment_date?->toDateString(),
                'purchase_id' => $payment->purchase_id,
                'purchase_number' => $payment->purchase?->invoice_number,
                'amount' => Money::decimal(Money::cents($payment->amount)),
                'notes' => $payment->notes,
            ]),
        ]]);
    }

    public function receive(StorePurchaseReceiptRequest $request, Supplier $supplier, ApiIdempotencyService $idempotency, PurchaseReceivingService $receiving): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);

        return $idempotency->execute($request->user(), 'suppliers.receipts.create', $key, ['supplier_id' => $supplier->id] + $data,
            function () use ($request, $supplier, $receiving, $data, $key): array {
                $purchase = $receiving->receive($supplier, $data, $request->user(), $key);

                return ['status' => 201, 'body' => ['data' => (new PurchaseReceiptResource($purchase))->resolve($request)]];
            });
    }

    public function store(CreateSupplierRequest $request, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);
        $openingBalance = number_format((int) round((float) ($data['opening_balance'] ?? 0) * 100) / 100, 2, '.', '');

        return $idempotency->execute(
            $request->user(),
            'suppliers.create',
            $key,
            $data,
            function () use ($request, $data, $openingBalance): array {
                $supplier = Supplier::query()->create([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'address' => $data['address'] ?? null,
                    'opening_balance' => $openingBalance,
                    'balance' => $openingBalance,
                    'notes' => $data['notes'] ?? null,
                ]);

                return [
                    'status' => 201,
                    'body' => ['data' => (new SupplierResource($supplier))->resolve($request)],
                ];
            }
        );
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);

        return $idempotency->execute($request->user(), 'suppliers.update', $key, ['supplier_id' => $supplier->id] + $data,
            function () use ($request, $supplier, $data): array {
                $updated = DB::transaction(function () use ($supplier, $data): Supplier {
                    $locked = Supplier::query()->lockForUpdate()->findOrFail($supplier->id);
                    $oldOpening = (int) round((float) $locked->opening_balance * 100);
                    $newOpening = (int) round((float) ($data['opening_balance'] ?? 0) * 100);
                    $newBalance = max(0, (int) round((float) $locked->balance * 100) + $newOpening - $oldOpening);
                    $locked->update([
                        'name' => $data['name'], 'phone' => $data['phone'] ?? null,
                        'email' => $data['email'] ?? null, 'address' => $data['address'] ?? null,
                        // Notes are omitted from API resources by design. A client that
                        // cannot read them must not erase them during an unrelated edit.
                        'notes' => array_key_exists('notes', $data) ? $data['notes'] : $locked->notes,
                        'opening_balance' => number_format($newOpening / 100, 2, '.', ''),
                        'balance' => number_format($newBalance / 100, 2, '.', ''),
                    ]);

                    return $locked->refresh()->loadCount('purchases');
                }, 3);

                return ['status' => 200, 'body' => ['data' => (new SupplierResource($updated))->resolve($request)]];
            });
    }

    public function destroy(
        DeleteSupplierRequest $request,
        Supplier $supplier,
        ApiIdempotencyService $idempotency,
        SupplierDeletionService $deletion,
    ): JsonResponse {
        $key = $request->validated('idempotency_key');

        return $idempotency->execute($request->user(), 'suppliers.delete', $key, ['supplier_id' => $supplier->id],
            function () use ($supplier, $deletion): array {
                $deletion->delete($supplier);

                return ['status' => 204, 'body' => []];
            });
    }
}
