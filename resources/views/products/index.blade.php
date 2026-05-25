@extends('layouts.app')
@section('title', ' - المنتجات')
@section('content')

<div class="page-header">
    <h1>المنتجات</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> منتج جديد
    </a>
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
