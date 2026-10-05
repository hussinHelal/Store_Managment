@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 56rem">
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> المنتجات</a>
        <h1 class="h3 mt-2">{{ $product->name }}</h1>
    </div>
    @if($product->image)
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3" style="max-width: 100%; max-height: 20rem; object-fit: contain">
    @endif
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">السعر</dt><dd class="col-sm-9">{{ number_format((float) $product->price, 2) }}</dd>
        <dt class="col-sm-3">المخزون</dt><dd class="col-sm-9">{{ $product->stock }}</dd>
        <dt class="col-sm-3">المبيعات</dt><dd class="col-sm-9">{{ $product->total_sold }}</dd>
        <dt class="col-sm-3">التصنيف</dt><dd class="col-sm-9">{{ $product->category?->name ?? '—' }}</dd>
        <dt class="col-sm-3">الباركود</dt><dd class="col-sm-9">{{ $product->barcode ?? '—' }}</dd>
        <dt class="col-sm-3">الوصف</dt><dd class="col-sm-9">{{ $product->description }}</dd>
        <dt class="col-sm-3">ملاحظات</dt><dd class="col-sm-9">{{ $product->notes ?: '—' }}</dd>
    </dl>
    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
@endsection
