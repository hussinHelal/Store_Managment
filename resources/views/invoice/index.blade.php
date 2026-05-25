@extends('layouts.app')
@section('title', '- الفواتير')
@section('content')

<div class="page-header">
    <h1>الفواتير</h1>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> فاتورة جديدة
    </a>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-dark">
      <tr>
            <th scope="col">#</th>
            <th scope="col">رقم الفاتورة</th>
            <th scope="col">العميل</th>
            <th scope="col">المنتج</th>
            <th scope="col">الكمية</th>
            <th scope="col">سعر الوحدة</th>
            <th scope="col">المبلغ</th>
            <th scope="col">المبلغ المدفوع</th>
            <th scope="col">التاريخ</th>
            <th scope="col">المبلغ الكلي</th>
            <th scope="col">الحالة</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @forelse($invoices as $invoice)
        <tr>
          <th scope="row">{{ $invoice->id }}</th>
          <td>{{ $invoice->invoice_number }}</td>
          <td>{{ $invoice->customer }}</td>
          <td>{{ $invoice->item_names ?: ($invoice->product?->name ?? 'محذوف') }}</td>
          <td>{{ $invoice->item_quantity ?: $invoice->quantity }}</td>
          <td>{{ $invoice->product?->price ?? $invoice->product_price }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->paid_amount }}</td>
          <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->status }}</td>
          <td>
              <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary m-1 rounded">تعديل</a>
              <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-sm btn-info m-1 rounded" title="طباعة الفاتورة"><i class="fas fa-print"></i></a>
              @if($invoice->status !== 'refunded')
              <form action="{{ route('invoices.refund', $invoice->id) }}" method="POST" style="display: inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-warning m-1 rounded">استرداد</button>
              </form>
              @endif
              <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1 rounded">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="text-center">لا توجد فواتير.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $invoices])
</div>

@endsection
