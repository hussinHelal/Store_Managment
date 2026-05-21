@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">انشاء دين</span>
    <form action="{{ route('installments.store') }}" method="POST">
      @csrf
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
        <input type="text" class="form-control" id="customer" name="customer">
      </div>

      <div class="mb-3">
        <label for="product_name" class="form-label">المنتج</label>
        <select name="product_id" class="form-control" required>
            @foreach($product as $p)
                <option value="{{ $p->id }}">{{ $p->name }} - {{ $p->price }} ج.م</option>
            @endforeach
        </select>
      </div>
      
      <div class="mb-3">
        <label for="product_price" class="form-label">سعر المنتج</label>
        <input type="number" class="form-control" id="product_price" name="product_price" >
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">الكمية</label>
        <input type="number" class="form-control" id="quantity" name="quantity" value="1">
      </div>

      <div class="mb-3">
        <label for="payment_date" class="form-label">تاريخ الدفع</label>
        <input type="date" class="form-control" id="payment_date" name="payment_date">
      </div>
      

      <div class="mb-3">
        <label for="paid_amount" class="form-label">المدفوع</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount">
      </div>

      <div class="mb-3">
        <label for="next_payment_date" class="form-label">تاريخ الدفع التالي</label>
        <input type="date" class="form-control" id="next_payment_date" name="next_payment_date">
      </div>

      <div class="mb-3">
        <label for="remaining" class="form-label">المتبقي</label>
        <input type="number" class="form-control" id="remaining" name="remaining" >
      </div>
      
      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>
    
    @push('scripts')
    <script>
        
      document.getElementById('product_name').addEventListener('change', function () {
      const prices = @json($product->pluck('price', 'id'));
      const productId = this.value;
      const price = prices[productId];
        document.getElementById('product_price').value = price;
      });

      document.getElementById('paid_amount').addEventListener('change', function () {
      const remaining = document.getElementById('product_price').value * (document.getElementById('quantity').value ?? 1) - document.getElementById('paid_amount').value;
        
        document.getElementById('remaining').value = remaining;
      });

      // 1. Grab the date elements
      const paymentDateInput = document.getElementById('payment_date');
      const nextPaymentDateInput = document.getElementById('next_payment_date');
      
      // 2. Prevent selecting past dates by setting the 'min' attribute to Today
      const today = new Date().toISOString().split('T')[0];
      paymentDateInput.min = today;
      
      // 3. Automatically calculate +1 month when payment_date changes
      paymentDateInput.addEventListener('change', function () {
        if (!this.value) {
          nextPaymentDateInput.value = '';
          return;
        }
      
        // Create a date object from the selected payment date
        const selectedDate = new Date(this.value);
      
        // Add 1 month
        selectedDate.setMonth(selectedDate.getMonth() + 1);
      
        // Format back to YYYY-MM-DD
        const year = selectedDate.getFullYear();
        // Months are 0-indexed in JS, so we add 1, and pad with a leading zero if needed
        const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
        const day = String(selectedDate.getDate()).padStart(2, '0');
      
        const nextMonthDate = `${year}-${month}-${day}`;
      
        // Set the value of the next payment date input
        nextPaymentDateInput.value = nextMonthDate;
      });
      
    </script>
    @endpush
    
@endsection
