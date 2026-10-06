@extends('layouts.app')

@section('title', ' - طباعة ملصق المنتج')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>طباعة ملصق المنتج</h1>
    <button type="button" class="btn btn-primary" onclick="window.print()">طباعة الملصق</button>
</div>

<div class="card p-4 border rounded shadow-sm" style="max-width: 600px; margin: 0 auto;">
    <div class="text-center mb-4">
        <h2 class="mb-1">{{ $product->name }}</h2>
        <p class="text-muted mb-1">{{ $product->category?->name ?? 'بدون صنف' }}</p>
        <p class="mb-0">السعر: <strong>{{ number_format($product->price, 2) }}</strong> ج.م</p>
    </div>

    <div class="text-center mb-3">
        <svg id="barcode"></svg>
        <div class="mt-2">{{ $product->barcode ?? $product->id }}</div>
    </div>

    <div class="d-flex justify-content-between border-top pt-3">
        <span>المخزون: <strong>{{ $product->stock }}</strong></span>
        <span>الباركود: <strong>{{ $product->barcode ?? '-' }}</strong></span>
    </div>
</div>

<div class="mt-4 text-center">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">العودة إلى المنتجات</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const code = '{{ addslashes($product->barcode ?? $product->id) }}';
        const barcodeSvg = document.getElementById('barcode');
        if (barcodeSvg && code) {
            JsBarcode(barcodeSvg, code, {
                format: 'CODE128',
                displayValue: true,
                fontSize: 18,
                height: 70,
                width: 2,
                margin: 10,
            });
        }
    });
</script>
@endpush
