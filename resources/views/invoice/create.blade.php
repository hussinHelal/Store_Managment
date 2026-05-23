@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">انشاء فاتورة</span>
    <form action="{{ route('invoices.store') }}" method="POST">
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
        <label for="customer" class="form-label">العميل</label>
        <input type="text" class="form-control" id="customer" name="customer">
      </div>

      {{-- <input type="hidden" id="product_id" name="product_id"> --}}
      <div class="mb-3">
        <label for="product_id" class="form-label">المنتج</label>
        <select class="form-select" dir='ltr' id="product_id" name="product_id">
          <option value="">اختر المنتج</option>
          @foreach($products as $product)
            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="invoice_date" class="form-label">التاريخ</label>
        <input type="date" class="form-control" id="invoice_date" name="invoice_date">
      </div>
      
      <div class="mb-3">
        <label for="quantity" class="form-label">الكمية</label>
        <input type="number" class="form-control" id="quantity" name="quantity">
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
    
     <div class="mt-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-danger">رجوع</a>
    </div>

    @push('scripts')
        <script>
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const totalInput = document.getElementById('total_amount');
        
        function calculateTotal() {
            let price = parseFloat(
                productSelect.options[productSelect.selectedIndex].dataset.price || 0
            );
        
            let quantity = parseInt(quantityInput.value || 0);
        
            totalInput.value = price * quantity;
        }
        
        productSelect.addEventListener('change', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);
        </script>
    @endpush
@endsection
