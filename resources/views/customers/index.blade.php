@extends('layouts.app')
@section('title', ' - العملاء')
@section('content')

<div class="page-header">
    <h1>العملاء</h1>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> عميل جديد
    </a>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
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
          @forelse($customers as $customer)
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
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">لا يوجد عملاء.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $customers])
</div>

@endsection
