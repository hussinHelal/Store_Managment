@extends('layouts.app')

@section('content')

    <table class="table table-borderless">
      <thead>
          <tr colspan="4">المبيعات</tr>
        <tr>
          <th scope="col">#</th>
          <th scope="col">اسم المنتج</th>
          <th scope="col">وصف المنتج</th>
          <th scope="col">الكمية المباعة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($sales as $sale)
        <tr>
          <th scope="row">{{ $sale->id }}</th>
          <td>{{ $sale->name }}</td>
          <td>{{ $sale->description }}</td>
          <td>{{ $sale->quantity }}</td>
          <td>
              <a href="{{ route('sale.edit', $sale->id) }}" class="btn btn-sm btn-primary">تعديل</a>
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