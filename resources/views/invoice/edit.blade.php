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
      <input type="hidden" id="product_id" name="product_id">

      <div class="mb-3">
        <label for="customer" class="form-label">العميل</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ $invoice->customer }}">
      </div>
      
      <div class="mb-3">
        <label for="product_id" class="form-label">المنتج</label>
        <select class="form-select" dir='ltr' id="product_id" name="product_id">
          <option value="">اختر المنتج</option>
          @foreach($products as $product)
            <option value="{{ $product->id }}" {{ $product->id == $invoice->product_id ? 'selected' : '' }}>{{ $product->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">الكمية</label>
        <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $invoice->quantity }}">
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


      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
    
     <div class="mt-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-danger">رجوع</a>
    </div>
    
@endsection