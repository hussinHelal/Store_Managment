@extends('layouts.app')
@section('title', ' - ' . $supplier->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('suppliers.index') }}" class="link-secondary text-decoration-none">الموردون</a>
            <h1 class="h3 mt-2 mb-1">{{ $supplier->name }}</h1>
            <div class="text-body-secondary">{{ $supplier->phone ?: 'بدون هاتف' }} @if($supplier->email) · {{ $supplier->email }} @endif</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('suppliers.statement', $supplier) }}" class="btn btn-outline-secondary">كشف الحساب والأعمار</a>
            @if(auth()->user()->can('page.suppliers.manage'))
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#receiptModal">استلام بضاعة من المورد</button>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen ms-1" aria-hidden="true"></i> تعديل البيانات</a>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#purchaseModal"><i class="fa-solid fa-file-circle-plus ms-1" aria-hidden="true"></i> فاتورة شراء</button>
                @if((float) $supplier->balance > 0)
                    <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fa-solid fa-money-bill-transfer ms-1" aria-hidden="true"></i> سداد دفعة</button>
                @endif
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">الرصيد الافتتاحي</div><div class="h4 mb-0">{{ number_format((float) $supplier->opening_balance, 2) }} ج.م</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">المستحق للمورد</div><div class="h4 mb-0 {{ (float) $supplier->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format((float) $supplier->balance, 2) }} ج.م</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">فواتير الشراء</div><div class="h4 mb-0">{{ $supplier->purchases_count }}</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary mb-2">العنوان</div><div class="mb-0">{{ $supplier->address ?: 'غير محدد' }}</div></div></div></div>
    </div>

    @if($supplier->notes)
        <p class="mb-4"><strong>ملاحظات المورد:</strong> {{ $supplier->notes }}</p>
    @endif

    <section class="mb-4" aria-labelledby="purchases-heading">
        <div class="d-flex justify-content-between align-items-center mb-2"><h2 id="purchases-heading" class="h5 mb-0">فواتير الشراء</h2></div>
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>طريقة الدفع</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th>إجراء</th></tr></thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td class="fw-semibold">{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->purchase_date?->format('Y-m-d') }}</td>
                        <td>{{ $purchase->payment_type === 'cash' ? 'كاش' : 'آجل' }}</td>
                        <td>{{ number_format((float) $purchase->total_amount, 2) }} ج.م</td>
                        <td>{{ number_format((float) $purchase->paid_amount, 2) }} ج.م</td>
                        <td>{{ number_format((float) $purchase->remaining, 2) }} ج.م</td>
                        <td><span class="badge {{ $purchase->statusBadgeClass() }}">{{ $purchase->statusLabel() }}</span></td>
                        <td>@if((float) $purchase->remaining > 0 && auth()->user()->can('page.suppliers.manage'))<button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#paymentModal" data-purchase-id="{{ $purchase->id }}" data-remaining="{{ $purchase->remaining }}">سداد</button>@else<span class="text-body-secondary">—</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-body-secondary py-4">لا توجد فواتير شراء مسجلة.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('components.pagination', ['collection' => $purchases])
    </section>

    <section aria-labelledby="payments-heading">
        <h2 id="payments-heading" class="h5 mb-2">سندات السداد</h2>
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>رقم السند</th><th>التاريخ</th><th>الفاتورة المرتبطة</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr><td class="fw-semibold">{{ $payment->receipt_number }}</td><td>{{ $payment->payment_date?->format('Y-m-d') }}</td><td>{{ $payment->purchase?->invoice_number ?: '—' }}</td><td>{{ number_format((float) $payment->amount, 2) }} ج.م</td><td>{{ $payment->notes ?: '—' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">لا توجد سندات سداد مسجلة.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('components.pagination', ['collection' => $payments])
    </section>
</div>

@if(auth()->user()->can('page.suppliers.manage'))
<div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('suppliers.purchases.store', $supplier) }}">
            @csrf
            <div class="modal-header"><h2 class="modal-title fs-5" id="purchaseModalTitle">تسجيل فاتورة شراء</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label for="purchase_date" class="form-label">تاريخ الفاتورة</label><input id="purchase_date" name="purchase_date" type="date" class="form-control" value="{{ old('purchase_date', now()->toDateString()) }}" required></div>
                <div class="mb-3"><label for="payment_type" class="form-label">طريقة الدفع</label><select id="payment_type" name="payment_type" class="form-select" required><option value="credit">آجل</option><option value="cash">كاش</option></select></div>
                <div class="mb-3"><label for="total_amount" class="form-label">إجمالي الفاتورة (ج.م)</label><input id="total_amount" name="total_amount" type="number" step="0.01" min="0.01" class="form-control" value="{{ old('total_amount') }}" required></div>
                <div class="mb-3"><label for="paid_amount" class="form-label">المدفوع الآن (ج.م، اختياري للآجل)</label><input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="form-control" value="{{ old('paid_amount', '0') }}"></div>
                <div><label for="purchase_notes" class="form-label">ملاحظات</label><textarea id="purchase_notes" name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ الفاتورة</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <form method="POST" action="{{ route('suppliers.receipts.store', $supplier) }}" id="purchase-receipt-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="modal-header"><h2 class="modal-title fs-5" id="receiptModalTitle">استلام فاتورة شراء</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label for="receipt_branch_id" class="form-label">فرع الاستلام</label><select class="form-select" id="receipt_branch_id" name="branch_id" required>@foreach($receiptBranches as $branch)<option value="{{ $branch->id }}" @selected($branch->is_main)>{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label for="receipt_date" class="form-label">تاريخ الفاتورة</label><input class="form-control" type="date" id="receipt_date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required></div>
                    <div class="col-md-4"><label for="receipt_payment_type" class="form-label">طريقة الدفع</label><select class="form-select" id="receipt_payment_type" name="payment_type"><option value="credit">آجل</option><option value="cash">نقدي بالكامل</option></select></div>
                </div>
                <div id="receipt-items" class="d-grid gap-2">
                    <div class="row g-2 align-items-start receipt-item border rounded p-2">
                        <div class="col-md-5"><label class="form-label">اسم المنتج</label><input class="form-control receipt-product-name" name="items[0][product_name]" list="receipt-product-options" autocomplete="off" required placeholder="اكتب اسم المنتج أو ابحث عنه"><input type="hidden" class="receipt-product-id" name="items[0][product_id]"><small class="text-body-secondary">يمكن تسجيل اسم يدوي، أو اختيار منتج موجود لتحديث مخزونه.</small></div>
                        <div class="col-md-2"><label class="form-label">الكمية</label><input class="form-control receipt-quantity" type="number" name="items[0][quantity]" min="1" max="1000000" value="1" required></div>
                        <div class="col-md-3"><label class="form-label">تكلفة الوحدة</label><input class="form-control receipt-unit-cost" type="number" name="items[0][unit_cost]" min="0" step="0.01" required><small class="text-warning receipt-cost-warning d-none">التكلفة تختلف عن تكلفة المنتج أو لم تُحدد.</small></div>
                        <div class="col-md-2"><button class="btn btn-outline-danger w-100 receipt-remove" type="button">حذف السطر</button></div>
                    </div>
                </div>
                <datalist id="receipt-product-options">@foreach($receiptProducts as $product)<option value="{{ $product->name }}" data-id="{{ $product->id }}" data-cost="{{ $product->cost_price ?? '' }}">{{ $product->barcode ? 'باركود '.$product->barcode : '' }}</option>@endforeach</datalist>
                <button class="btn btn-outline-primary mt-3" type="button" id="receipt-add-item">إضافة منتج</button>
                <div class="mt-3"><label for="receipt_paid_amount" class="form-label">المدفوع الآن (ج.م)</label><input class="form-control" type="number" min="0" step="0.01" name="paid_amount" id="receipt_paid_amount" value="0.00"></div>
                <div class="h5 mt-3">إجمالي الفاتورة: <span id="receipt-total">0.00</span> ج.م</div>
                <div><label for="receipt_notes" class="form-label">ملاحظات</label><textarea class="form-control" name="notes" id="receipt_notes" rows="2" maxlength="2000"></textarea></div>
                <p class="small text-body-secondary mt-3 mb-0">تُحدّث كمية الفرع عند اختيار منتج مسجل. البنود المكتوبة يدوياً تحفظ في الفاتورة كسجل تاريخي دون إنشاء منتج أو تغيير المخزون. تكلفة الفاتورة لا تستبدل تكلفة المنتج.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button class="btn btn-primary" type="submit">حفظ واستلام البضاعة</button></div>
        </form>
    </div></div>
</div>

@if((float) $supplier->balance > 0)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}">
            @csrf
            <div class="modal-header"><h2 class="modal-title fs-5" id="paymentModalTitle">سداد دفعة للمورد</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
                <input type="hidden" name="purchase_id" id="payment_purchase_id">
                <p class="text-body-secondary">الرصيد المستحق: <strong>{{ number_format((float) $supplier->balance, 2) }} ج.م</strong></p>
                <div class="mb-3"><label for="payment_amount" class="form-label">مبلغ السداد (ج.م)</label><input id="payment_amount" name="amount" type="number" step="0.01" min="0.01" max="{{ $supplier->balance }}" class="form-control" value="{{ old('amount') }}" required></div>
                <div class="mb-3"><label for="payment_date" class="form-label">تاريخ السداد</label><input id="payment_date" name="payment_date" type="date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required></div>
                <div><label for="payment_notes" class="form-label">ملاحظات</label><textarea id="payment_notes" name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-success">تأكيد السداد</button></div>
        </form>
    </div></div>
</div>
@endif
@endif

@if(auth()->user()->can('page.suppliers.manage'))
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('purchase-receipt-form');
        if (!form) return;
        const rows = document.getElementById('receipt-items');
        const total = document.getElementById('receipt-total');
        const paid = document.getElementById('receipt_paid_amount');
        const paymentType = document.getElementById('receipt_payment_type');
        const update = () => {
            let sum = 0;
            rows.querySelectorAll('.receipt-item').forEach((row) => {
                const quantity = Number(row.querySelector('.receipt-quantity').value) || 0;
                const costInput = row.querySelector('.receipt-unit-cost');
                const unitCost = Number(costInput.value) || 0;
                sum += Math.round(unitCost * 100) * quantity;
                const nameInput = row.querySelector('.receipt-product-name');
                const match = [...document.querySelectorAll('#receipt-product-options option')].find(option => option.value === nameInput.value);
                row.querySelector('.receipt-product-id').value = match?.dataset.id || '';
                const referenceCost = match?.dataset.cost;
                const warning = row.querySelector('.receipt-cost-warning');
                warning.classList.toggle('d-none', referenceCost !== undefined && referenceCost !== '' && Number(referenceCost) > 0 && Math.round(Number(referenceCost) * 100) === Math.round(unitCost * 100));
            });
            total.textContent = (sum / 100).toFixed(2);
            if (paymentType.value === 'cash') paid.value = (sum / 100).toFixed(2);
        };
        document.getElementById('receipt-add-item').addEventListener('click', () => {
            const row = rows.querySelector('.receipt-item').cloneNode(true);
            const index = rows.querySelectorAll('.receipt-item').length;
            row.querySelectorAll('[name]').forEach((input) => input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`));
            row.querySelector('.receipt-product-name').value = '';
            row.querySelector('.receipt-product-id').value = '';
            row.querySelector('.receipt-quantity').value = '1';
            row.querySelector('.receipt-unit-cost').value = '';
            delete row.querySelector('.receipt-unit-cost').dataset.autofilled;
            row.querySelector('.receipt-cost-warning').classList.add('d-none');
            rows.appendChild(row);
            update();
        });
        rows.addEventListener('click', (event) => {
            if (!event.target.closest('.receipt-remove') || rows.querySelectorAll('.receipt-item').length <= 1) return;
            event.target.closest('.receipt-item').remove();
            update();
        });
        rows.addEventListener('input', update);
        rows.addEventListener('input', (event) => {
            if (event.target.matches('.receipt-product-name')) {
                const match = [...document.querySelectorAll('#receipt-product-options option')].find(option => option.value === event.target.value);
                const cost = match?.dataset.cost;
                const input = event.target.closest('.receipt-item').querySelector('.receipt-unit-cost');
                event.target.closest('.receipt-item').querySelector('.receipt-product-id').value = match?.dataset.id || '';
                if (cost !== undefined && cost !== '') {
                    input.value = cost;
                    input.dataset.autofilled = 'true';
                }
            }
            update();
        });
        paymentType.addEventListener('change', update);
        const paymentModal = document.getElementById('paymentModal');
        paymentModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const id = trigger?.dataset.purchaseId || '';
            const remaining = trigger?.dataset.remaining || '';
            document.getElementById('payment_purchase_id').value = id;
            const amount = document.getElementById('payment_amount');
            amount.max = remaining || @json((string) $supplier->balance);
            amount.value = remaining || '';
            document.getElementById('paymentModalTitle').textContent = id ? 'سداد فاتورة شراء' : 'سداد دفعة للمورد';
        });
        update();
    });
    </script>
    @endpush
@endif

@if($errors->any())
    @push('scripts')<script>document.addEventListener('DOMContentLoaded', () => { const preferred = @json(session('open_modal', 'receiptModal')); const target = document.getElementById(preferred) || document.querySelector('#receiptModal') || document.querySelector('#paymentModal') || document.querySelector('#purchaseModal'); if (target) bootstrap.Modal.getOrCreateInstance(target).show(); });</script>@endpush
@endif
@endsection
