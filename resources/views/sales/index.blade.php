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
          @foreach($sales as $sale)
        <tr>
          <th scope="row">{{ $sale->id }}</th>
          <td>{{ $sale->name }}</td>
          <td>{{ $sale->quantity }}</td>
          <td>
              <form action="{{ route('sale.destroy', $sale->id) }}" method="POST" style="display: inline;">
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