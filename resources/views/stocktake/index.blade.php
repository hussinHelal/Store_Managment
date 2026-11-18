@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h1 class="h3 mb-1">جرد المخزون</h1><p class="text-body-secondary mb-0">ابدأ جرداً محفوظاً على رصيد لحظي ثم راجع الفروق قبل اعتمادها</p></div><a class="btn btn-outline-secondary" href="{{ route('branch_stock.index') }}">مخزون الفروع</a></div>
    @can('page.stocktake.manage')
    <form class="card mb-3" method="post" action="{{ route('stocktake.store') }}">@csrf<input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><div class="card-body"><div class="row g-3 align-items-end"><div class="col-12"><h2 class="h5 mb-0">بدء جرد جديد</h2></div>
        <div class="col-md-4"><label class="form-label" for="take-branch">الفرع</label><select class="form-select" id="take-branch" name="branch_id" required><option value="">اختر الفرع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label" for="take-scope">النطاق</label><select class="form-select" id="take-scope" name="scope"><option value="all">كل المنتجات</option><option value="category">تصنيف محدد</option></select></div>
        <div class="col-md-3"><label class="form-label" for="take-category">التصنيف</label><select class="form-select" id="take-category" name="category_id"><option value="">اختر التصنيف عند الحاجة</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label" for="take-note">ملاحظة</label><input class="form-control" id="take-note" name="note" maxlength="500"></div><div class="col-12"><button class="btn btn-primary">بدء الجرد</button></div></div></div>
    </form>
    @endcan
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>رقم الجرد</th><th>الفرع</th><th>النطاق</th><th>عدد المنتجات</th><th>الحالة</th><th>تاريخ البدء</th><th></th></tr></thead><tbody>
    @forelse($stocktakes as $take)
        @php($statusLabel = ['draft' => 'مسودة', 'counting' => 'قيد العد', 'review' => 'قيد المراجعة', 'approved' => 'معتمد', 'cancelled' => 'ملغى'][$take->status] ?? $take->status)
        <tr><td>{{ $take->number }}</td><td>{{ $take->branch->name }}</td><td>{{ $take->scope === 'all' ? 'كل المنتجات' : ($take->category?->name ?? 'تصنيف') }}</td><td>{{ $take->items_count }}</td><td>{{ $statusLabel }}</td><td>{{ $take->created_at->timezone('Africa/Cairo')->format('Y-m-d H:i') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('stocktake.show', $take) }}">فتح</a></td></tr>
    @empty<tr><td colspan="7" class="text-center text-body-secondary py-5">لم يبدأ أي جرد بعد.</td></tr>@endforelse
    </tbody></table></div>{{ $stocktakes->links() }}
</div>
@endsection
