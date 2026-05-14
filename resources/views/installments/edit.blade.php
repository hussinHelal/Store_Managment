@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تعديل دين</span>
    <form action="{{ route('installments.update', $installment) }}" method="POST">
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
      
      <div class="mb-3">
        <label for="customer" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ $installment->customer }}" readonly>
      </div>
      
      <div class="mb-3">
        <label for="amount" class="form-label">المبلغ</label>
        <input type="number" class="form-control" id="amount" name="amount" value="{{ $installment->amount }}">
      </div>
      
      <div class="mb-3">
        <label for="due_date" class="form-label">تاريخ الانشاء</label>
        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $installment->due_date }}">
      </div>

      <div class="mb-3">
        <label for="payment_date" class="form-label">تاريخ الدفع</label>
        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ $installment->payment_date }}">
      </div>
      <div class="mb-3">
        <label for="paid_amount" class="form-label">المدفوع</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ $installment->paid_amount }}">
      </div>

      <div class="mb-3">
        <label for="next_payment_date" class="form-label">تاريخ الدفع التالي</label>
        <input type="date" class="form-control" id="next_payment_date" name="next_payment_date" value="{{ $installment->next_payment_date }}">
      </div>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
    
    
@endsection