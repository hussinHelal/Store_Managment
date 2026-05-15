@extends('layouts.app')

@section('content')
    
<div class="row justify-content-center">
    <span class="text-center text-bold ">المنتجات</span>
</div>

    <table class="table table-borderless table-hover table-striped table-primary">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">اسم المنتج</th>
          <th scope="col">الكمية المباعة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($products as $product)
        <tr>
          <th scope="row">{{ $product->id }}</th>
          <td>{{ $product->name }}</td>
          <td>{{ $product->total_sold }}</td>
          <td>
              <form action="{{ route('sales.destroy', $product->id) }}" method="POST" style="display: inline;">
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