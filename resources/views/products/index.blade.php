@extends('layouts.app')
@section('title', ' - المنتجات')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>المنتجات</h1>
        @include('components.search-bar', ['placeholder' => 'ابحث باسم المنتج أو الباركود'])
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> منتج جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي المنتجات</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1">{{ $products->total() }}</h2>
            <p class="text-secondary mb-0">عرض {{ $products->count() }} من أصل {{ $products->total() }} منتج.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">السعر</th>
          <th scope="col">الوصف</th>
          <th scope="col">الصنف</th>
          <th scope="col">الباركود</th>
          <th scope="col">المخزون</th>
          <th scope="col">الصورة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @forelse($products as $product)
        <tr>
          <th scope="row">{{ $product->id }}</th>
          <td>{{ $product->name }}</td>
          <td>{{ $product->price }}</td>
          <td>{{ $product->description }}</td>
          <td>{{ $product->category?->name ?? 'لا يوجد' }}</td>
          <td>{{ $product->barcode ?? '-' }}</td>
          <td>{{ $product->stock }}</td>
          <td>
              @if($product->image)
                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="Product Image" class="img-fluid rounded" style="max-width: 60px; height: 60px; object-fit: cover;">
              @else
                <span class="text-muted">لا توجد صورة</span>
              @endif
          </td>
          <td>
              <a href="{{ route('products.printLabel', $product->id) }}" target="_blank" class="btn btn-sm btn-info rounded">طباعة</a>
              <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary rounded">تعديل</a>
              <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">لا توجد منتجات.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $products])
</div>

@endsection
