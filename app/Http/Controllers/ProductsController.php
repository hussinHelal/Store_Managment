<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Models\Branch;
use App\Models\category;
use App\Models\installments;
use App\Models\invoice;
use App\Models\products;
use App\Models\StockMovement;
use App\Models\TrackedDevice;
use App\Services\CurrentBranch;
use App\Services\PictureStorageService;
use App\Services\StockService;
use App\Support\ProductCostSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:80']]);
        $term = trim((string) $request->input('search', ''));

        $products = products::with('category')
            ->when($term !== '', function ($query) use ($term) {
                // % and _ typed by the user are literal characters, not wildcards.
                $search = '%'.addcslashes($term, '%_\\').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('barcode', 'like', $search);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $categories = category::all();

        $supportsCostPrice = ProductCostSchema::available();

        return view('products.index', compact('products', 'categories', 'supportsCostPrice'));
    }

    public function printLabel(products $product)
    {
        return view('products.print-label', compact('product'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = category::all();
        $supportsCostPrice = ProductCostSchema::available();

        return view('products.create', compact('categories', 'supportsCostPrice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveProductRequest $request, PictureStorageService $pictures, StockService $stock, CurrentBranch $currentBranch)
    {
        $validated = $request->validated();
        ProductCostSchema::prepare($validated, $request);
        $newImage = $request->hasFile('image') ? $pictures->store($request->file('image'), 'products') : null;
        unset($validated['remove_image']);
        if ($newImage !== null) {
            $validated['image'] = $newImage;
        }

        $openingQuantity = (int) $validated['stock'];
        $validated['stock'] = 0;

        try {
            DB::transaction(function () use ($validated, $openingQuantity, $request, $stock): void {
                $product = products::query()->create($validated);
                foreach (Branch::query()->where('is_active', true)->pluck('id') as $branchId) {
                    $stock->initializeBranch((int) $product->id, (int) $branchId);
                }
                if ($openingQuantity > 0) {
                    $stock->increase(
                        (int) $product->id, $openingQuantity, 'opening', $request->user(),
                        'رصيد افتتاحي عند إنشاء المنتج', $product,
                    );
                }
            }, 3);
        } catch (\Throwable $exception) {
            $pictures->delete($newImage, 'products');
            throw $exception;
        }

        return redirect()->route('products.index')->with('success', 'تمت إضافة المنتج.');
    }

    /**
     * Display the specified resource.
     */
    public function show(products $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(products $product)
    {
        $categories = category::all();
        $supportsCostPrice = ProductCostSchema::available();

        return view('products.edit', compact('product', 'categories', 'supportsCostPrice') + ['id' => $product->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveProductRequest $request, products $product, PictureStorageService $pictures, StockService $stock)
    {
        $validated = $request->validated();
        ProductCostSchema::prepare($validated, $request);
        $targetStock = (int) $validated['stock'];
        $stockReason = trim((string) ($validated['stock_adjustment_reason'] ?? ''));
        unset($validated['stock'], $validated['stock_adjustment_reason']);
        $oldImage = $product->image;
        $removeImage = (bool) ($validated['remove_image'] ?? false);
        $newImage = $request->hasFile('image') ? $pictures->store($request->file('image'), 'products') : null;
        unset($validated['remove_image']);
        if ($removeImage && $newImage === null) {
            $validated['image'] = null;
        } elseif ($newImage !== null) {
            $validated['image'] = $newImage;
        }

        try {
            DB::transaction(function () use ($product, $validated, $targetStock, $stockReason, $stock, $request): void {
                $locked = products::query()->lockForUpdate()->findOrFail($product->id);
                $delta = $targetStock - (int) $locked->stock;
                if ($delta !== 0 && mb_strlen($stockReason) < 5) {
                    throw ValidationException::withMessages([
                        'stock_adjustment_reason' => 'اكتب سبباً واضحاً عند تغيير كمية المخزون.',
                    ]);
                }
                $locked->update($validated);
                if ($delta !== 0) {
                    $stock->setAggregate(
                        (int) $locked->id, $targetStock, $request->user(), $stockReason,
                        $locked,
                    );
                }
            }, 3);
        } catch (\Throwable $exception) {
            $pictures->delete($newImage, 'products');
            throw $exception;
        }

        if ($newImage !== null || $removeImage) {
            $pictures->delete($oldImage, 'products');
        }

        return redirect()->route('products.index')->with('success', 'تم تحديث المنتج.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, products $product, PictureStorageService $pictures)
    {
        if ($request->user()->cannot('delete-products', $product)) {
            return redirect()->route('products.index')->with('error', 'لا تملك صلاحية حذف هذا المنتج.');
        }

        if ($this->hasTransactionHistory($product)) {
            return redirect()->route('products.index')
                ->with('error', 'لا يمكن حذف منتج له سجل في الفواتير أو الأقساط أو المبيعات.');
        }

        $image = $product->image;
        $product->delete();
        $pictures->delete($image, 'products');

        return redirect()->route('products.index')->with('success', 'تم حذف المنتج.');
    }

    /**
     * Invoices store only their FIRST product in invoices.product_id; every other line lives
     * in the JSON "items" column. Checking the relations alone let a product that was sold as
     * a later line be deleted, which then broke editing and refunding that invoice.
     */
    private function hasTransactionHistory(products $product): bool
    {
        $line = '%"product_id":'.(int) $product->id.',%';

        return $product->invoices()->exists()
            || $product->installments()->exists()
            || $product->sales()->exists()
            || invoice::query()->where('items', 'like', $line)->exists()
            || installments::query()->where('items', 'like', $line)->exists()
            || TrackedDevice::query()->where('product_id', $product->id)->exists()
            || StockMovement::query()->where('product_id', $product->id)->exists();
    }
}
