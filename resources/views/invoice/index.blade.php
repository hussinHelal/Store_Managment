@extends('layouts.app')

@section('content')


    <button class="btn btn-primary"><a href="{{ route('invoices.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> فاتورة جديدة</a></button>
     <div class="row justify-content-center">
        <span class="text-center text-bold">الفواتير</span>
    </div>
    <table class="table table-borderless table-hover table-striped table-primary">
      <tr>
          <thead>
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
          <td>{{ $invoice->products?->name }}</td>
          <td>{{ $invoice->quantity }}</td>
          <td>{{ $invoice->products?->price }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->paid_amount }}</td>
          <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->status }}</td>
          <td>
              <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection
