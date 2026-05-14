@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('products.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> منتج جديد</a></button>

    <div class="row justify-content-center">
        <span class="text-center text-bold">المنتجات</span>
    </div>

    <table class="table table-borderless table-hover table-striped table-primary">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">السعر</th>
          <th scope="col">الوصف</th>
          <th scope="col">الصنف</th>
          <th scope="col">المخزون</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($products as $product)
        <tr>
          <th scope="row">{{ $product->id }}</th>
          <td>{{ $product->name }}</td>
          <td>{{ $product->price }}</td>
          <td>{{ $product->description }}</td>
          <td>{{ $product->category?->name ?? 'بدون صنف'}}</td>
          <td>{{ $product->stock }}</td>
          <td>
              <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج ؟')">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection