@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h1 class="h3 mb-1">حركات المخزون</h1><p class="text-body-secondary mb-0">سجل تدقيقي للزيادات والنواقص، لا يمكن تعديل أو حذف الحركات</p></div><a class="btn btn-outline-secondary" href="{{ route('branch_stock.index') }}">العودة إلى مخزون الفروع</a></div>
    <form class="card card-body mb-3" method="get"><div class="row g-2 align-items-end">
        <div class="col-md-2"><label class="form-label" for="movement-branch">الفرع</label><select id="movement-branch" class="form-select" name="branch_id"><option value="">الكل</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label" for="movement-product">المنتج</label><select id="movement-product" class="form-select" name="product_id"><option value="">كل المنتجات</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label" for="movement-type">نوع الحركة</label><select id="movement-type" class="form-select" name="type"><option value="">الكل</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $type }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label" for="movement-from">من تاريخ</label><input id="movement-from" class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label" for="movement-to">إلى تاريخ</label><input id="movement-to" class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">تصفية</button></div>
    </div></form>
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>التاريخ</th><th>الفرع</th><th>المنتج</th><th>النوع</th><th>التغير</th><th>الرصيد بعد الحركة</th><th>المستخدم</th><th>السبب / المرجع</th></tr></thead><tbody>
    @forelse($movements as $movement)<tr><td>{{ $movement->created_at?->timezone('Africa/Cairo')->format('Y-m-d H:i') }}</td><td>{{ $movement->branch->name }}</td><td>{{ $movement->product->name }}</td><td>{{ $movement->type }}</td><td class="{{ $movement->quantity_delta < 0 ? 'text-danger' : 'text-success' }}">{{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}</td><td>{{ $movement->balance_after }}</td><td>{{ $movement->creator?->name ?? 'النظام' }}</td><td>{{ $movement->note ?? ($movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : '—') }}</td></tr>
    @empty<tr><td colspan="8" class="text-center text-body-secondary py-5">لا توجد حركات مسجلة ضمن هذه المرشحات.</td></tr>@endforelse
    </tbody></table></div>
    {{ $movements->links() }}
</div>
@endsection
