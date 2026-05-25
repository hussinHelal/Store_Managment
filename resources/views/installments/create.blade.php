@extends('layouts.app')

@section('content')

@php
    $installmentItems = [];
    if (old('product_ids')) {
        $oldProductIds = old('product_ids');
        $oldQuantities = old('quantities', []);
        foreach ($oldProductIds as $index => $productId) {
            $product = $products->firstWhere('id', $productId);
            $installmentItems[] = [
                'product_id' => $productId,
                'quantity'   => intval($oldQuantities[$index] ?? 1),
                'price'      => $product?->price ?? 0,
                'name'       => $product?->name ?? '',
            ];
        }
    } else {
        $installmentItems[] = ['product_id' => '', 'quantity' => 1, 'price' => 0, 'name' => ''];
    }
@endphp

    <span class="text-center border border-1 rounded text-bold">انشاء دين</span>
    <form action="{{ route('installments.store') }}" method="POST">
      @csrf


      <div class="mb-3">
        <label for="customer" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ old('customer') }}">
      </div>

      <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label class="form-label mb-0">المنتجات</label>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addInstallmentItemBtn">إضافة منتج</button>
        </div>
        <div id="installment-items-container"></div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="total_quantity" class="form-label">الكمية الإجمالية</label>
          <input type="number" readonly class="form-control" id="total_quantity" name="quantity" value="{{ old('quantity', 1) }}">
        </div>
        <div class="col-md-4">
          <label for="payment_date" class="form-label">تاريخ الدفع</label>
          <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ old('payment_date') }}">
        </div>
        <div class="col-md-4">
          <label for="paid_amount" class="form-label">المدفوع</label>
          <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="product_price" class="form-label">المجموع</label>
          <input type="number" readonly class="form-control" id="product_price" name="product_price" value="{{ old('product_price', 0) }}">
        </div>
        <div class="col-md-4">
          <label for="next_payment_date" class="form-label">تاريخ الدفع التالي</label>
          <input type="date" class="form-control" id="next_payment_date" name="next_payment_date" value="{{ old('next_payment_date') }}">
        </div>
        <div class="col-md-4">
          <label for="remaining" class="form-label">المتبقي</label>
          <input type="number" readonly class="form-control" id="remaining" name="remaining" value="{{ old('remaining', 0) }}">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>

     <div class="mt-3">
        <a href="{{ route('installments.index') }}" class="btn btn-danger">رجوع</a>
    </div>

    @push('scripts')
    <script>
      const installmentProducts = @json($products->map(fn($product) => ['id' => $product->id, 'name' => $product->name, 'price' => $product->price]));
      const installmentItemsContainer = document.getElementById('installment-items-container');
      const totalQuantityInput = document.getElementById('total_quantity');
      const totalPriceInput = document.getElementById('product_price');
      const paidAmountInput = document.getElementById('paid_amount');
      const remainingInput = document.getElementById('remaining');
      const paymentDateInput = document.getElementById('payment_date');
      const nextPaymentDateInput = document.getElementById('next_payment_date');

      function formatPrice(value) {
          return Number(value || 0).toFixed(2);
      }

      function updateInstallmentTotals() {
          let totalQty = 0;
          let totalPrice = 0;

          installmentItemsContainer.querySelectorAll('.installment-item-row').forEach(row => {
              const qty = parseInt(row.querySelector('input[name="quantities[]"]').value || 0);
              const lineTotal = parseFloat(row.querySelector('.line-total-input').value || 0);
              totalQty += qty;
              totalPrice += lineTotal;
          });

          totalQuantityInput.value = totalQty;
          totalPriceInput.value = formatPrice(totalPrice);
          const paidValue = parseFloat(paidAmountInput.value || 0);
          remainingInput.value = formatPrice(Math.max(0, totalPrice - paidValue));
      }

      function createInstallmentItemRow(item = { product_id: '', quantity: 1, price: 0 }) {
          const row = document.createElement('div');
          row.className = 'row g-3 installment-item-row align-items-end mb-2';

          const productOptions = installmentProducts.map(product => {
              const selected = product.id == item.product_id ? 'selected' : '';
              return `<option value="${product.id}" data-price="${product.price}" ${selected}>${product.name} - ${product.price} ج.م</option>`;
          }).join('');

          row.innerHTML = `
            <div class="col-md-5">
              <label class="form-label">المنتج</label>
              <select name="product_ids[]" class="form-select product-select">
                <option value="">اختر المنتج</option>
                ${productOptions}
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">الكمية</label>
              <input type="number" name="quantities[]" class="form-control item-quantity" min="1" value="${item.quantity}">
            </div>
            <div class="col-md-3">
              <label class="form-label">المجموع</label>
              <input type="text" readonly class="form-control line-total-input" value="${formatPrice(item.price * item.quantity)}">
            </div>
            <div class="col-md-1 text-end">
              <button type="button" class="btn btn-danger remove-installment-item" title="حذف المنتج">×</button>
            </div>
          `;

          const select = row.querySelector('.product-select');
          const quantityInput = row.querySelector('.item-quantity');
          const lineTotalInput = row.querySelector('.line-total-input');

          select.addEventListener('change', () => {
              const product = installmentProducts.find(p => p.id == select.value);
              const price = product ? product.price : 0;
              const quantity = parseInt(quantityInput.value || 0);
              lineTotalInput.value = formatPrice(price * quantity);
              updateInstallmentTotals();
          });

          quantityInput.addEventListener('input', () => {
              const product = installmentProducts.find(p => p.id == select.value);
              const price = product ? product.price : 0;
              lineTotalInput.value = formatPrice(price * parseInt(quantityInput.value || 0));
              updateInstallmentTotals();
          });

          row.querySelector('.remove-installment-item').addEventListener('click', () => {
              row.remove();
              if (!installmentItemsContainer.querySelector('.installment-item-row')) {
                  createInstallmentItemRow();
              }
              updateInstallmentTotals();
          });

          installmentItemsContainer.appendChild(row);
          if (item.product_id) {
              select.value = item.product_id;
          }
      }

      document.getElementById('addInstallmentItemBtn').addEventListener('click', () => {
          createInstallmentItemRow();
      });

      paidAmountInput.addEventListener('input', updateInstallmentTotals);

      const initialInstallmentItems = @json($installmentItems);
      initialInstallmentItems.forEach(item => createInstallmentItemRow(item));
      updateInstallmentTotals();

      const today = new Date().toISOString().split('T')[0];
      paymentDateInput.min = today;
      paymentDateInput.addEventListener('change', function () {
          if (!this.value) {
              nextPaymentDateInput.value = '';
              return;
          }
          const selectedDate = new Date(this.value);
          selectedDate.setMonth(selectedDate.getMonth() + 1);
          const year = selectedDate.getFullYear();
          const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
          const day = String(selectedDate.getDate()).padStart(2, '0');
          nextPaymentDateInput.value = `${year}-${month}-${day}`;
      });
    </script>
    @endpush

@endsection
