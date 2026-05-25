@extends('layouts.app')

@section('content')

@php
    $invoiceItems = [];
    if (old('product_ids')) {
        $oldProductIds = old('product_ids');
        $oldQuantities = old('quantities', []);
        foreach ($oldProductIds as $index => $productId) {
            $product = $products->firstWhere('id', $productId);
            $invoiceItems[] = [
                'product_id' => $productId,
                'quantity'   => intval($oldQuantities[$index] ?? 1),
                'price'      => $product?->price ?? 0,
                'name'       => $product?->name ?? '',
            ];
        }
    } elseif (is_array($invoice->items) && count($invoice->items)) {
        $invoiceItems = $invoice->items;
    } else {
        $invoiceItems[] = [
            'product_id' => $invoice->product_id,
            'quantity'   => $invoice->quantity,
            'price'      => $invoice->product?->price ?? 0,
            'name'       => $invoice->product?->name ?? '',
        ];
    }
@endphp

    <span class="text-center border border-1 rounded text-bold">تحديث فاتورة</span>
    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
      @csrf
      @method('PUT')


      <div class="mb-3">
        <label for="customer" class="form-label">العميل</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ old('customer', $invoice->customer) }}">
      </div>

      <div class="mb-3">
        <label for="barcode" class="form-label">باركود (اختياري)</label>
        <input type="text" class="form-control" id="barcode" name="barcode" placeholder="امسح الباركود ثم اضغط Enter للاضافة" autofocus>
      </div>

      <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label class="form-label mb-0">المنتجات</label>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addInvoiceItemBtn">إضافة منتج</button>
        </div>
        <div id="invoice-items-container"></div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="total_quantity" class="form-label">الكمية الإجمالية</label>
          <input type="number" readonly class="form-control" id="total_quantity" name="quantity" value="{{ old('quantity', $invoice->quantity) }}">
        </div>
        <div class="col-md-4">
          <label for="invoice_date" class="form-label">التاريخ</label>
          <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date) }}">
        </div>
        <div class="col-md-4">
          <label for="paid_amount" class="form-label">المبلغ المدفوع</label>
          <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $invoice->paid_amount) }}">
        </div>
      </div>

      <div class="mb-3">
        <label for="total_amount" class="form-label">المبلغ الكلي</label>
        <input type="number" readonly class="form-control" id="total_amount" name="total_amount" value="{{ old('total_amount', $invoice->total_amount) }}">
      </div>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

    <div class="mt-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-danger">رجوع</a>
    </div>

    @push('scripts')
    <script>
      const allProducts = @json($products->map(fn($product) => ['id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'barcode' => $product->barcode]));
      const invoiceItemsContainer = document.getElementById('invoice-items-container');
      const barcodeInput = document.getElementById('barcode');
      const totalQuantityInput = document.getElementById('total_quantity');
      const totalAmountInput = document.getElementById('total_amount');
      const paidAmountInput = document.getElementById('paid_amount');

      function formatPrice(value) {
          return Number(value || 0).toFixed(2);
      }

      function updateInvoiceTotals() {
          let totalQty = 0;
          let totalAmt = 0;

          invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach(row => {
              const qty = parseInt(row.querySelector('input[name="quantities[]"]').value || 0);
              const lineTotal = parseFloat(row.querySelector('.line-total-input').value || 0);
              totalQty += qty;
              totalAmt += lineTotal;
          });

          totalQuantityInput.value = totalQty;
          totalAmountInput.value = formatPrice(totalAmt);
          const paidValue = parseFloat(paidAmountInput.value || 0);
          if (paidValue > totalAmt) {
              paidAmountInput.value = formatPrice(totalAmt);
          }
      }

      function createInvoiceItemRow(item = { product_id: '', quantity: 1, price: 0 }) {
          const row = document.createElement('div');
          row.className = 'row g-3 invoice-item-row align-items-end mb-2';

          const productOptions = allProducts.map(product => {
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
              <button type="button" class="btn btn-danger remove-invoice-item" title="حذف المنتج">×</button>
            </div>
          `;

          const select = row.querySelector('.product-select');
          const quantityInput = row.querySelector('.item-quantity');
          const lineTotalInput = row.querySelector('.line-total-input');

          select.addEventListener('change', () => {
              const product = allProducts.find(p => p.id == select.value);
              const price = product ? product.price : 0;
              const quantity = parseInt(quantityInput.value || 0);
              lineTotalInput.value = formatPrice(price * quantity);
              updateInvoiceTotals();
          });

          quantityInput.addEventListener('input', () => {
              const product = allProducts.find(p => p.id == select.value);
              const price = product ? product.price : 0;
              lineTotalInput.value = formatPrice(price * parseInt(quantityInput.value || 0));
              updateInvoiceTotals();
          });

          row.querySelector('.remove-invoice-item').addEventListener('click', () => {
              row.remove();
              if (!invoiceItemsContainer.querySelector('.invoice-item-row')) {
                  createInvoiceItemRow();
              }
              updateInvoiceTotals();
          });

          invoiceItemsContainer.appendChild(row);
          if (item.product_id) {
              select.value = item.product_id;
          }
      }

      document.getElementById('addInvoiceItemBtn').addEventListener('click', () => {
          createInvoiceItemRow();
      });

      const initialInvoiceItems = @json($invoiceItems);
      initialInvoiceItems.forEach(item => createInvoiceItemRow(item));
      updateInvoiceTotals();

        if (barcodeInput) {
          barcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
              e.preventDefault();
              const code = barcodeInput.value.trim();
              if (!code) return;
              const found = allProducts.find(p => p.barcode == code);
              if (found) {
                createInvoiceItemRow({ product_id: found.id, quantity: 1, price: found.price });
                updateInvoiceTotals();
                barcodeInput.value = '';
              } else {
                alert('لم يتم العثور على منتج لهذا الباركود');
              }
            }
          });
            // autofocus for easy scanner use
            barcodeInput.focus();
        }
    </script>
    @endpush
@endsection
