@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h1 class="h3 mb-1">جرد رقم {{ $stocktake->number }}</h1><p class="text-body-secondary mb-0">{{ $stocktake->branch->name }} — لقطة النظام {{ $stocktake->snapshot_at?->timezone('Africa/Cairo')->format('Y-m-d H:i') }}</p></div><a class="btn btn-outline-secondary" href="{{ route('stocktake.index') }}">قائمة الجرد</a></div>
    @if($stocktake->status === 'counting')
        <form class="mb-3" method="post" action="{{ route('stocktake.review', $stocktake) }}" onsubmit="return confirm('إنهاء العد وعرض الفروقات للمراجعة؟ يمكن تصحيح الكميات قبل اعتماد الجرد.')">@csrf<button class="btn btn-primary">إنهاء العد وإرسال للمراجعة</button></form>
    @elseif($stocktake->status === 'review')
        <div class="alert alert-warning">راجع الفروق قبل الاعتماد. يتم احتساب الحركات التي تمت بعد العد للحفاظ على دقة الرصيد الحالي.</div>
        @can('page.stocktake.approve')<form method="post" action="{{ route('stocktake.approve', $stocktake) }}" onsubmit="return confirm('اعتماد فروق الجرد وتسجيل حركات تسوية؟')">@csrf<button class="btn btn-success">اعتماد الجرد وتطبيق الفروق</button></form>@endcan
    @endif
    @can('page.stocktake.manage')
        @if(in_array($stocktake->status, ['counting', 'review'], true))<form class="my-3 d-flex gap-2" method="post" action="{{ route('stocktake.cancel', $stocktake) }}" onsubmit="const r=prompt('سبب إلغاء الجرد؟'); if(!r)return false; this.reason.value=r; return confirm('تأكيد الإلغاء؟')">@csrf<input type="hidden" name="reason"><button class="btn btn-outline-danger">إلغاء الجرد</button></form>@endif
    @endcan
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>المنتج</th><th>الباركود</th><th>رصيد النظام وقت البدء</th><th>الكمية المعدودة</th><th>الفرق المعتمد</th><th>ملاحظة</th><th>حفظ العد</th></tr></thead><tbody>
    @forelse($stocktake->items as $item)
        <tr><td>{{ $item->product->name }}</td><td>{{ $item->product->barcode ?? '—' }}</td><td>{{ $item->system_qty }}</td>
            <td>@if($item->counted_qty !== null){{ $item->counted_qty }}@else — @endif</td><td>{{ $item->difference ?? '—' }}</td><td>{{ $item->note ?? '—' }}</td>
            <td>@if(in_array($stocktake->status, ['counting', 'review'], true) && auth()->user()->can('page.stocktake.manage'))<form class="row g-1 align-items-center" method="post" action="{{ route('stocktake.count', [$stocktake, $item->id]) }}">@csrf @method('put')<div class="col-12 col-xl-4"><input class="form-control form-control-sm" type="number" name="counted_qty" min="0" max="1000000" value="{{ old('counted_qty', $item->counted_qty) }}" required aria-label="الكمية المعدودة"></div><div class="col-12 col-xl-5"><input class="form-control form-control-sm" name="note" maxlength="500" value="{{ old('note', $item->note) }}" placeholder="ملاحظة" aria-label="ملاحظة"></div><div class="col-12 col-xl-3"><button class="btn btn-sm btn-primary w-100">حفظ العدد</button></div></form>@else — @endif</td>
        </tr>
    @empty<tr><td colspan="7" class="text-center text-body-secondary py-5">لا توجد منتجات في نطاق هذا الجرد.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
