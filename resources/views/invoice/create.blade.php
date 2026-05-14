@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">انشاء فاتورة</span>
    <form action="{{ route('invoices.store') }}" method="POST">
      @csrf
      
      <div class="mb-3">
        <label for="customer" class="form-label">العميل</label>
        <input type="text" class="form-control" id="customer" name="customer">
      </div>
      
      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <input type="text" class="form-control" id="description" name="description">
      </div>

      <div class="mb-3">
        <label for="invoice_date" class="form-label">التاريخ</label>
        <input type="date" class="form-control" id="invoice_date" name="invoice_date">
      </div>
      
      <div class="mb-3">
        <label for="paid_amount" class="form-label">المبلغ المدفوع</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount">
      </div>
      
      <div class="mb-3">
        <label for="total_amount" class="form-label">المبلغ الكلي</label>
        <input type="number" class="form-control" id="total_amount" name="total_amount">
      </div>


      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>
    
    
@endsection