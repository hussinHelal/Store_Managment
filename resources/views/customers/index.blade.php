@extends('layouts.app')
@section('title', ' - العملاء')
@section('content')
    
<button class="btn btn-primary"><a href="{{ route('customers.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> عميل جديد</a></button>

<div class="row justify-content-center">
    <span class="fw-bold text-body text-center">العملاء</span>
</div>
<table class="table  table-hover table-striped ">
      <thead class="table-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">الاسم</th>
            <th scope="col">التليفون</th>
            <th scope="col">العنوان</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($customers as $customer)
        <tr>
          <th scope="row">{{ $customer->id }}</th>
          <td>{{ $customer->name }}</td>
          <td>{{ $customer->phone }}</td>
          <td>{{ $customer->address }}</td>
          <td>
              <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-primary rounded">تعديل</a>
              <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا العميل ؟')">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection