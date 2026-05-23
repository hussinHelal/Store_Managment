@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card rounded-4 shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">فاتورة</h4>
                    <p class="text-muted mb-0">رقم الفاتورة: <strong>{{ $invoice->invoice_number }}</strong></p>
                </div>
                <div class="text-end">
                    <h5 class="mb-1">{{ config('app.name') }}</h5>
                    <p class="text-muted mb-0">نسخة قابلة للطباعة</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="bg-light rounded-4 p-3">
                        <h6 class="mb-2">العميل</h6>
                        <p class="mb-1"><strong>الاسم:</strong> {{ $invoice->customer }}</p>
                        <p class="mb-0"><strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded-4 p-3">
                        <h6 class="mb-2">المنتج</h6>
                        <p class="mb-1"><strong>الاسم:</strong> {{ $invoice->product?->name ?? 'محذوف' }}</p>
                        <p class="mb-0"><strong>السعر الواحد:</strong> {{ number_format($invoice->product_price, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $invoice->product?->name ?? 'محذوف' }}</td>
                            <td>{{ $invoice->quantity }}</td>
                            <td>{{ number_format($invoice->product_price, 2) }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded-4" style="background: rgba(34, 197, 94, 0.08);">
                        <h6 class="mb-2">الحالة</h6>
                        <p class="mb-0 text-success fw-semibold">{{ ucfirst($invoice->status) }}</p>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="p-3 rounded-4" style="background: rgba(59, 130, 246, 0.08);">
                        <h6 class="mb-2">المبلغ المدفوع</h6>
                        <p class="mb-0 fw-semibold">{{ number_format($invoice->paid_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-lg rounded-4">
                    <i class="fa-solid fa-print me-2"></i> طباعة الفاتورة
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-lg rounded-4">العودة</a>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endsection
