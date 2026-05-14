@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تحديث فاتورة</span>
    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
      @csrf
      @method('PUT')
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0" style="list-style-type: none; padding: 0;">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
      
      <div class="mb-3">
        <label for="customer" class="form-label">العميل</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ $invoice->customer }}">
      </div>
      
      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <input type="text" class="form-control" id="description" name="description" value="{{ $invoice->description }}">
      </div>

      <div class="mb-3">
        <label for="invoice_date" class="form-label">التاريخ</label>
        <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date }}">
      </div>
      
      <div class="mb-3">
        <label for="paid_amount" class="form-label">المبلغ المدفوع</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ $invoice->paid_amount }}">
      </div>
      
      <div class="mb-3">
        <label for="total_amount" class="form-label">المبلغ الكلي</label>
        <input type="number" class="form-control" id="total_amount" name="total_amount" value="{{ $invoice->total_amount }}">
      </div>


      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>
    
    
@endsection