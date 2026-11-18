@extends('layouts.app')
@section('title', ' - الصيانة')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div><h1>طلبات الصيانة</h1><form method="GET" action="{{ route('maintenance.index') }}" class="mt-3 mb-4 d-flex gap-2" role="search"><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="ابحث باسم الجهاز"><button class="btn btn-outline-secondary" type="submit">بحث</button></form></div>
        <a href="{{ route('maintenance.create') }}" class="btn btn-primary btn-round"><i class="fas fa-plus ms-1"></i> طلب صيانة جديد</a>
    </div>
    <div class="card border-0 shadow-sm bg-body p-3 mb-4"><div class="text-muted small">إجمالي طلبات الصيانة</div><div class="fs-3 fw-bold">{{ $maintenances->total() }}</div><div class="text-secondary">عرض {{ $maintenances->count() }} من {{ $maintenances->total() }} طلب</div></div>
    <div class="table-wrapper table-responsive"><table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark"><tr><th>#</th><th>صورة الجهاز</th><th>الجهاز</th><th>الوصف</th><th>الملاحظات</th><th>المالك</th><th>العنوان</th><th>الهاتف</th><th>الحالة</th><th>تاريخ الاستلام</th><th>تاريخ الإنجاز</th><th>الإجراءات</th></tr></thead>
      <tbody>
      @forelse($maintenances as $maintenance)
        @php $statusLabel = match($maintenance->status) { 'قيد الانتظار' => 'قيد الانتظار', 'مكتمل' => 'مكتمل', 'مرفوض' => 'مرفوض', default => $maintenance->status }; @endphp
        <tr><th scope="row">{{ $maintenance->id }}</th><td>@if($maintenance->image_path)<a href="{{ app(\App\Services\PictureStorageService::class)->url($maintenance->image_path, 'maintenance') }}" target="_blank" rel="noopener"><img src="{{ app(\App\Services\PictureStorageService::class)->url($maintenance->image_path, 'maintenance', true) }}" alt="صورة جهاز الصيانة" class="img-thumbnail" style="width:64px;height:64px;object-fit:cover"></a>@else<span class="text-body-secondary">لا توجد صورة</span>@endif</td><td>{{ $maintenance->name }}</td><td>{{ $maintenance->description }}</td><td>{{ $maintenance->notes ?: '—' }}</td><td>{{ $maintenance->owner }}</td><td>{{ $maintenance->address }}</td><td>{{ $maintenance->phone }}</td>
          <td><span class="badge {{ $statusLabel === 'مرفوض' ? 'text-bg-danger' : ($statusLabel === 'مكتمل' ? 'text-bg-success' : 'text-bg-warning') }}">{{ $statusLabel }}</span></td>
          <td>{{ $maintenance->requested_date?->format('Y-m-d') }}</td><td>{{ $maintenance->completed_date?->format('Y-m-d') ?? '—' }}</td>
          <td class="text-nowrap"><a href="{{ route('maintenance.show', $maintenance) }}" class="btn btn-sm btn-outline-secondary">عرض</a> <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-sm btn-primary">تعديل</a> <a href="{{ route('maintenance.showRepaired', $maintenance) }}" class="btn btn-sm btn-outline-warning">تغيير الحالة</a>
            <form action="{{ route('maintenance.destroy', $maintenance) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل تريد حذف طلب الصيانة؟')">حذف</button></form>
          </td></tr>
      @empty
        <tr><td colspan="12" class="text-center py-4">لا توجد طلبات صيانة.</td></tr>
      @endforelse
      </tbody>
    </table>
    @include('components.pagination', ['collection' => $maintenances])
    </div>
</div>
@endsection
