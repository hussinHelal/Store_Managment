@extends('layouts.app')
@section('title', '- المنتجات المباعة')
@section('content')

<div class="page-header">
    <h1>المنتجات المباعة</h1>
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
