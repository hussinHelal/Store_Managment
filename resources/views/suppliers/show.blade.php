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
            @if(auth()->user()->can('page.suppliers.manage'))
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
                <thead class="table-light"><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>طريقة الدفع</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
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
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">لا توجد فواتير شراء مسجلة.</td></tr>
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

@if((float) $supplier->balance > 0)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}">
            @csrf
            <div class="modal-header"><h2 class="modal-title fs-5" id="paymentModalTitle">سداد دفعة للمورد</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>
            <div class="modal-body">
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

@if($errors->any())
    @push('scripts')<script>document.addEventListener('DOMContentLoaded', () => { const target = document.querySelector('#paymentModal') || document.querySelector('#purchaseModal'); if (target) bootstrap.Modal.getOrCreateInstance(target).show(); });</script>@endpush
@endif
@endsection
