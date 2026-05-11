@extends('layouts.app')

@section('content')

    <table class="table table-borderless">
      <thead>
          <tr colspan="5">العملاء</tr>
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
              <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" style="display: inline;">
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