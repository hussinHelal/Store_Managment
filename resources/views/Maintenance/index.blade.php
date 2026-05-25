@extends('layouts.app')
@section('title', ' - الصيانة')
@section('content')

<div class="page-header">
    <h1>الصيانة</h1>
    <a href="{{ route('maintenance.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> جهاز جديد
    </a>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">اسم الجهاز</th>
            <th scope="col">الوصف</th>
            <th scope="col">المالك</th>
            <th scope="col">عنوان المالك</th>
            <th scope="col">تليفون</th>
            <th scope="col">الحالة</th>
            <th scope="col">تاريخ الدخول</th>
            <th scope="col">تاريخ الخروج</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @forelse($maintenances as $maintenance)
        <tr>
          <th scope="row">{{ $maintenance->id }}</th>
          <td>{{ $maintenance->name }}</td>
          <td>{{ $maintenance->description }}</td>
          <td>{{ $maintenance->owner }}</td>
          <td>{{ $maintenance->address }}</td>
          <td>{{ $maintenance->phone }}</td>
          <td>{{ $maintenance->status }}</td>
          <td>{{ \Carbon\Carbon::parse($maintenance->requested_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($maintenance->completed_date)->format('Y-m-d') }}</td>
          <td>
              <a href="{{ route('maintenance.edit', $maintenance->id) }}" class="btn btn-sm btn-primary rounded">تعديل</a>
              <a href="{{ route('maintenance.showRepaired', $maintenance->id) }}" class="btn btn-sm btn-primary rounded">تغيير الحالة</a>
              <form action="{{ route('maintenance.destroy', $maintenance->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا الجهاز؟')">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">لا توجد طلبات صيانة.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $maintenances])
</div>

@endsection
