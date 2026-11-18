@extends('layouts.app')
@section('title', 'مواعيد التركيب')
@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="page-header"><div><h1>مواعيد التركيب</h1><p class="text-secondary mb-0">حجوزات العملاء والفنيين وحالة تنفيذ الخدمة.</p></div></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @can('page.installations.manage')
        <details class="card shadow-sm mb-4" @if(request()->boolean('create') || $errors->any()) open @endif><summary class="card-header fw-bold" style="cursor:pointer">حجز موعد تركيب جديد</summary><div class="card-body">@include('installation_appointments.form', ['appointment'=>null])</div></details>
    @endcan
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5"><label class="visually-hidden" for="appointment-search">بحث</label><input id="appointment-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="بحث باسم العميل أو الهاتف أو الجهاز"></div>
        <div class="col-md-3"><label class="visually-hidden" for="appointment-date">التاريخ</label><input id="appointment-date" type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}"></div>
        <div class="col-md-2"><label class="visually-hidden" for="appointment-status">الحالة</label><select id="appointment-status" name="status" class="form-select"><option value="">كل الحالات</option>@foreach(['scheduled'=>'مجدول','confirmed'=>'مؤكد','in_progress'=>'جارٍ التنفيذ','completed'=>'مكتمل','cancelled'=>'ملغي'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['status'] ?? '')===$key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary">بحث</button><a class="btn btn-outline-secondary" href="{{ route('installations.index') }}">مسح</a></div>
    </form>
    <div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead><tr><th>الموعد</th><th>العميل</th><th>الجهاز والخدمة</th><th>الفني والمدة</th><th>الحالة</th><th>التفاصيل</th><th>الإجراءات</th></tr></thead><tbody>
    @forelse($appointments as $appointment)
        <tr><td>{{ $appointment->scheduled_at->format('Y-m-d H:i') }}</td><td>{{ $appointment->customer_name }}<div dir="ltr" class="small text-secondary">{{ $appointment->phone }}</div></td><td>{{ $appointment->device_model }}<div class="small text-secondary">{{ $appointment->service_type }}</div></td><td>{{ $appointment->technician?->name ?? 'غير محدد' }}<div class="small text-secondary">{{ $appointment->duration_minutes }} دقيقة</div></td><td>{{ ['scheduled'=>'مجدول','confirmed'=>'مؤكد','in_progress'=>'جارٍ التنفيذ','completed'=>'مكتمل','cancelled'=>'ملغي'][$appointment->status] }}</td><td>{{ $appointment->service_details ?: '—' }}@if($appointment->address)<div class="small text-secondary">{{ $appointment->address }}</div>@endif</td>
        <td>@can('page.installations.manage')<details><summary class="btn btn-sm btn-outline-primary">تعديل</summary><div class="card card-body mt-2" style="min-width:min(80vw,900px)">@include('installation_appointments.form',['appointment'=>$appointment])</div></details><form class="mt-2" action="{{ route('installations.destroy',$appointment) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الموعد؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>@else<span class="text-secondary">عرض فقط</span>@endcan</td></tr>
    @empty<tr><td colspan="7" class="text-center py-4">لا توجد مواعيد مطابقة.</td></tr>@endforelse
    </tbody></table></div>{{ $appointments->links() }}
</div>
@endsection
