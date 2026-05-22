@extends('layouts.app')
@section('content')
    <span class="text-center border border-1 rounded text-bold">دفع قسط</span>
    <form action="{{ route('installments.pay', $installment) }}" method="POST">
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
        <label class="form-label">سعر المنتج</label>
        <input type="number" class="form-control" value="{{ $installment->product_price }}" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">إجمالي المدفوع سابقاً</label>
        <input type="number" class="form-control" value="{{ $installment->paid_amount }}" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">المتبقي الحالي</label>
        <input type="number" class="form-control" value="{{ $installment->remaining }}" readonly>
      </div>

      <div class="mb-3">
        <label for="paid_amount" class="form-label">المبلغ المدفوع الآن</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount" 
               value="0" min="1" max="{{ $installment->remaining }}">
      </div>

      <div class="mb-3">
        <label for="remaining_after" class="form-label">المتبقي بعد الدفع</label>
        <input type="number" class="form-control" id="remaining_after" 
               value="{{ $installment->remaining }}" readonly>
      </div>

      <button type="submit" class="btn btn-primary">دفع</button>
    </form>
    <div class="mt-3">
        <a href="{{ route('installments.index') }}" class="btn btn-danger">رجوع</a>
    </div>
    @push('scripts')
    <script>
      document.getElementById('paid_amount').addEventListener('input', function () {
          const currentRemaining = {{ $installment->remaining }};
          const payNow = parseFloat(this.value) || 0;
          const remainingAfter = currentRemaining - payNow;
          document.getElementById('remaining_after').value = remainingAfter;
      });
    </script>
    @endpush

@endsection