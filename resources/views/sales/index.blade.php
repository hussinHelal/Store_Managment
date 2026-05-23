@extends('layouts.app')
@section('title', '- المنتجات المباعة')
@section('content')
    
<div class="row justify-content-center">
    <span class="fw-bold text-body text-center ">المنتجات</span>
</div>

    <table class="table  table-hover table-striped ">
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
          <th scope="row">{{ $loop->iteration }}</th>
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
    
@endsection