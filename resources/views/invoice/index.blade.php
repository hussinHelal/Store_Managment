@extends('layouts.app')

@section('content')


    <button class="btn btn-primary"><a href="{{ route('invoices.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> فاتورة جديدة</a></button>
     <div class="row justify-content-center">
        <span class="fw-bold text-body text-center">الفواتير</span>
    </div>
    <table class="table  table-hover table-striped ">
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
          @foreach($invoices as $invoice)
        <tr>
          <th scope="row">{{ $invoice->id }}</th>
          <td>{{ $invoice->invoice_number }}</td>
          <td>{{ $invoice->customer }}</td>
          <td>{{ $invoice->product?->name }}</td>
          <td>{{ $invoice->quantity }}</td>
          <td>{{ $invoice->product?->price }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->paid_amount }}</td>
          <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->status }}</td>
          <td>
              <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              @if($invoice->status !== 'refunded')
              <form action="{{ route('invoices.refund', $invoice->id) }}" method="POST" style="display: inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-warning m-1 ">استرداد</button>
              </form>
              @endif
              <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1 ">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection
