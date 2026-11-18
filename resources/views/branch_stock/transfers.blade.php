@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h1 class="h3 mb-1">تحويلات المخزون بين الفروع</h1><p class="text-body-secondary mb-0">يُخصم المخزون عند الإرسال ويُضاف للوجهة عند الاستلام</p></div><a class="btn btn-outline-secondary" href="{{ route('branch_stock.index') }}">مخزون الفروع</a></div>
    @can('page.branch_stock.manage')
    <form class="card card-body mb-3" method="post" action="{{ route('branch_stock.transfers.store') }}">
        @csrf <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <h2 class="h5">إنشاء تحويل مخزون</h2>
        <div class="row g-2 mb-3"><div class="col-md-5"><label class="form-label" for="from-branch">من فرع</label><select class="form-select" id="from-branch" name="from_branch_id" required><option value="">اختر الفرع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div><div class="col-md-5"><label class="form-label" for="to-branch">إلى فرع</label><select class="form-select" id="to-branch" name="to_branch_id" required><option value="">اختر الفرع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label" for="transfer-note">ملاحظة</label><input id="transfer-note" class="form-control" name="note" maxlength="500"></div></div>
        <div id="transfer-lines" class="d-grid gap-2">
            <div class="row g-2 transfer-line"><div class="col-md-8"><label class="form-label">المنتج</label><select class="form-select" name="items[0][product_id]" required><option value="">اختر المنتج</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} — المتاح إجمالاً: {{ $product->stock }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">الكمية</label><input class="form-control" type="number" name="items[0][quantity]" min="1" max="1000000" required></div><div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-danger remove-line" type="button" aria-label="حذف السطر">حذف</button></div></div>
        </div>
        <div class="mt-2"><button id="add-transfer-line" class="btn btn-sm btn-outline-secondary" type="button">إضافة منتج</button><button class="btn btn-primary me-2">حفظ كمسودة</button></div>
    </form>
    @endcan
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>الرقم</th><th>المصدر</th><th>الوجهة</th><th>الأصناف</th><th>الحالة</th><th>التاريخ</th><th>الإجراءات</th></tr></thead><tbody>
    @forelse($transfers as $transfer)
        @php($statusLabel = ['draft' => 'مسودة', 'sent' => 'تم الإرسال', 'received' => 'مستلم', 'cancelled' => 'ملغى'][$transfer->status] ?? $transfer->status)
        <tr><td>{{ $transfer->number }}</td><td>{{ $transfer->source->name }}</td><td>{{ $transfer->destination->name }}</td><td>@foreach($transfer->items as $item)<div>{{ $item->product->name }} × {{ $item->quantity }}</div>@endforeach</td><td><span class="badge text-bg-secondary">{{ $statusLabel }}</span></td><td>{{ $transfer->created_at->timezone('Africa/Cairo')->format('Y-m-d H:i') }}</td><td class="d-flex flex-wrap gap-1">
            @can('page.branch_stock.manage')
            @if($transfer->status === 'draft')<form method="post" action="{{ route('branch_stock.transfers.send', $transfer) }}" onsubmit="return confirm('إرسال التحويل؟ سيتم خصم الكميات المتاحة الآن.')">@csrf<button class="btn btn-sm btn-primary">إرسال</button></form><form method="post" action="{{ route('branch_stock.transfers.destroy', $transfer) }}" onsubmit="return confirm('حذف مسودة التحويل؟')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger">حذف المسودة</button></form>@endif
            @if($transfer->status === 'sent')<form method="post" action="{{ route('branch_stock.transfers.receive', $transfer) }}" onsubmit="return confirm('تأكيد استلام جميع الأصناف؟')">@csrf<button class="btn btn-sm btn-success">استلام</button></form><form method="post" action="{{ route('branch_stock.transfers.cancel', $transfer) }}" onsubmit="const r=prompt('سبب إلغاء التحويل؟'); if(!r)return false; this.reason.value=r; return confirm('إلغاء التحويل وإرجاع الكمية للمصدر؟')">@csrf<input type="hidden" name="reason"><button class="btn btn-sm btn-outline-danger">إلغاء وإرجاع</button></form>@endif
            @endcan
        </td></tr>
    @empty<tr><td colspan="7" class="text-center text-body-secondary py-5">لا توجد تحويلات حتى الآن.</td></tr>@endforelse
    </tbody></table></div>{{ $transfers->links() }}
</div>
<script>
(() => { const list=document.getElementById('transfer-lines'); const add=document.getElementById('add-transfer-line'); if(!list||!add)return; let next=list.querySelectorAll('.transfer-line').length; const options=list.querySelector('select').innerHTML; add.addEventListener('click',()=>{const row=document.createElement('div');row.className='row g-2 transfer-line';row.innerHTML='<div class="col-md-8"><label class="form-label">المنتج</label><select class="form-select" name="items['+next+'][product_id]" required>'+options+'</select></div><div class="col-md-3"><label class="form-label">الكمية</label><input class="form-control" type="number" name="items['+next+'][quantity]" min="1" max="1000000" required></div><div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-danger remove-line" type="button">حذف</button></div>';list.appendChild(row);next++;});list.addEventListener('click',event=>{if(event.target.closest('.remove-line')&&list.querySelectorAll('.transfer-line').length>1)event.target.closest('.transfer-line').remove();});})();
</script>
@endsection
