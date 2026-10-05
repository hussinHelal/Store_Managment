@extends('layouts.app')

@section('content')
@php
    $items = is_array($invoice->items) && $invoice->items !== []
        ? $invoice->items
        : [['name' => $invoice->product?->name ?? '—', 'quantity' => $invoice->quantity, 'price' => $invoice->product_price, 'line_total' => $invoice->total_amount]];
@endphp
<div class="container py-4" style="max-width: 64rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('invoices.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الفواتير</a>
            <h1 class="h3 mt-2">فاتورة {{ $invoice->invoice_number }}</h1>
        </div>
        <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-outline-primary"><i class="fa-solid fa-print me-1" aria-hidden="true"></i> طباعة</a>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">العميل</dt><dd class="col-sm-9">{{ $invoice->customer }}</dd>
        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9">{{ $invoice->invoice_date?->format('Y-m-d') }}</dd>
        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9">{{ ['paid' => 'مدفوعة', 'unpaid' => 'غير مدفوعة', 'refunded' => 'مستردة'][$invoice->status] ?? $invoice->status }}</dd>
        <dt class="col-sm-3">ملاحظات</dt><dd class="col-sm-9">{{ $invoice->notes ?: '—' }}</dd>
    </dl>
    <div class="table-responsive border rounded">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
            <tbody>
                @foreach($items as $item)
                    <tr><td>{{ $item['name'] ?? '—' }}</td><td>{{ $item['quantity'] ?? 0 }}</td><td>{{ number_format((float) ($item['price'] ?? 0), 2) }}</td><td>{{ number_format((float) ($item['line_total'] ?? 0), 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <dl class="row mt-3">
        <dt class="col-sm-3">الإجمالي</dt><dd class="col-sm-9">{{ number_format((float) $invoice->total_amount, 2) }}</dd>
        <dt class="col-sm-3">المدفوع</dt><dd class="col-sm-9">{{ number_format((float) $invoice->paid_amount, 2) }}</dd>
    </dl>
</div>
@endsection
