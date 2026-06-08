@extends('layouts.app')
@section('title', '- المنتجات المباعة')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>المنتجات المباعة</h1>
        @include('components.search-bar', ['placeholder' => 'ابحث باسم المنتج'])
    </div>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي المنتجات المباعة</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1">{{ $soldProducts->total() }}</h2>
            <p class="text-secondary mb-0">عرض {{ $soldProducts->count() }} من أصل {{ $soldProducts->total() }} سجلات.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">اسم المنتج</th>
          <th scope="col">الكمية المباعة</th>
        </tr>
      </thead>
      <tbody>
          @forelse($soldProducts as $product)
        <tr>
          <th scope="row">{{ $product->id }}</th>
          <td>{{ $product->name }}</td>
          <td>{{ $product->sold_quantity }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="text-center">لا توجد منتجات مباعة حتى الآن.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $soldProducts])
</div>

@endsection
