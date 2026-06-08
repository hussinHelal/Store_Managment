@extends('layouts.app')
@section('title', ' - الصيانة')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>الصيانة</h1>
        @include('components.search-bar', ['placeholder' => 'اسم الجهاز'])
    </div>
    <a href="{{ route('maintenance.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> جهاز جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي طلبات الصيانة</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1">{{ $maintenances->total() }}</h2>
            <p class="text-secondary mb-0">عرض {{ $maintenances->count() }} من أصل {{ $maintenances->total() }} طلب.</p>
        </div>
    </div>
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
