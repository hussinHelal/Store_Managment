@extends('layouts.app')

@section('content')

    
    <button class="btn btn-primary"><a href="{{ route('product.create') }}" class="text-white"><i class="fas fa-plus"></i> منتج جديد</a></button>
    <table class="table table-borderless">
      <thead>
          <tr colspan="6">المنتجات</tr>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">السعر</th>
          <th scope="col">الوصف</th>
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
          <td>{{ $product->stock }}</td>
          <td>
              <a href="{{ route('product.edit', $product->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('product.destroy', $product->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection