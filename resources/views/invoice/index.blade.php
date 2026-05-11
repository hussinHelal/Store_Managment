@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('invoice.create') }}" class="text-white"><i class="fas fa-plus"></i> فاتورة جديدة</a></button>
    <table class="table table-borderless">
      <thead>
          <tr colspan="7">الفواتير</tr>
        <tr>
          <th scope="col">#</th>
          <th >رقم الفاتورة</th>
          <th >العميل</th>
          <th >الوصف</th>
          <th >التاريخ</th>
          <th > المبلغ الكلي</th>
          <th > الحالة</th>
          <th >الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($invoices as $invoice)
        <tr>
          <th scope="row">{{ $invoice->id }}</th>
          <td>{{ $invoice->invoice_number }}</td>
          <td>{{ $invoice->customer }}</td>
          <td>{{ $invoice->description }}</td>
          <td>{{ $invoice->invoice_date }}</td>
          <td>{{ $invoice->total_amount }}</td>
          <td>{{ $invoice->status }}</td>
          <td>
              <a href="{{ route('invoice.edit', $invoice->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('invoice.destroy', $invoice->id) }}" method="POST" style="display: inline;">
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