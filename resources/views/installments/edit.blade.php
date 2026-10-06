@extends('layouts.app')

@section('content')
<style>
  .installment-product-results { position: absolute; inset-inline: 0; top: 100%; z-index: 1080; max-height: 16rem; overflow-y: auto; }
</style>

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
    } elseif (is_array($installment->items) && count($installment->items)) {
        $installmentItems = $installment->items;
    } else {
        $installmentItems[] = [
            'product_id' => $installment->product_id,
            'quantity'   => $installment->quantity,
            'price'      => $installment->product?->price ?? $installment->product_price ?? 0,
            'name'       => $installment->product?->name ?? $installment->product_name ?? '',
        ];
    }
@endphp

    <span class="text-center border border-1 rounded text-bold">تعديل دين</span>
    <form action="{{ route('installments.update', $installment) }}" method="POST">
      @csrf
      @method('PUT')


      <div class="mb-3">
        <label for="customer" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="customer" name="customer" value="{{ old('customer', $installment->customer) }}" >
      </div>

      <div class="mb-3">
        <label for="notes" class="form-label">ملاحظات</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes', $installment->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
          <input type="number" readonly class="form-control" id="total_quantity" name="quantity" value="{{ old('quantity', $installment->quantity) }}">
        </div>

        <div class="col-md-4">
          <label for="payment_date" class="form-label">تاريخ الدفع</label>
          <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ old('payment_date', optional($installment->payment_date)->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4">
          <label for="paid_amount" class="form-label">المدفوع</label>
          <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $installment->paid_amount) }}">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="product_price" class="form-label">المجموع</label>
          <input type="number" readonly class="form-control" id="product_price" name="product_price" value="{{ old('product_price', $installment->product_price) }}">
        </div>

        <div class="col-md-4">
          <label for="next_payment_date" class="form-label">تاريخ الدفع التالي</label>
          <input type="date" class="form-control" id="next_payment_date" name="next_payment_date" value="{{ old('next_payment_date', optional($installment->next_payment_date)->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4">
          <label for="remaining" class="form-label">المتبقي</label>
          <input type="number" readonly class="form-control" id="remaining" name="remaining" value="{{ old('remaining', $installment->remaining) }}">
        </div>
      </div>

      @include('components.form-actions', ['submitLabel' => 'تحديث', 'backUrl' => route('installments.index')])
    </form>

    @push('scripts')
    <script>
      const installmentProducts = @json($installmentProducts ?? []);
      const installmentProductSearchUrl = @json(route('installments.products.search'));
      const installmentItemsContainer = document.getElementById('installment-items-container');
      const totalQuantityInput = document.getElementById('total_quantity');
      const totalPriceInput = document.getElementById('product_price');
      const paidAmountInput = document.getElementById('paid_amount');
      const remainingInput = document.getElementById('remaining');
      const paymentDateInput = document.getElementById('payment_date');
      const nextPaymentDateInput = document.getElementById('next_payment_date');

        function escapeHtml(value) {
          return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
          })[character]);
        }

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
          const selectedProduct = installmentProducts.find(product => product.id == item.product_id);
          const initialName = item.name || selectedProduct?.name || '';
          const initialPrice = item.price ?? selectedProduct?.price ?? 0;

          row.innerHTML = `
            <div class="col-md-5">
              <label class="form-label">المنتج</label>
              <div class="position-relative">
                <input type="text" class="form-control product-search" autocomplete="off" placeholder="اكتب حرفين للبحث عن منتج" value="${escapeHtml(initialName)}" aria-expanded="false">
                <input type="hidden" name="product_ids[]" class="product-id" value="${escapeHtml(item.product_id || '')}" data-price="${escapeHtml(initialPrice)}">
                <div class="list-group installment-product-results shadow d-none" role="listbox"></div>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label">الكمية</label>
              <input type="number" name="quantities[]" class="form-control item-quantity" min="1" value="${escapeHtml(item.quantity)}">
            </div>
            <div class="col-md-3">
              <label class="form-label">المجموع</label>
              <input type="text" readonly class="form-control line-total-input" value="${formatPrice(item.price * item.quantity)}">
            </div>
            <div class="col-md-1 text-end">
              <button type="button" class="btn btn-danger remove-installment-item" title="حذف المنتج">×</button>
            </div>
          `;

            const searchInput = row.querySelector('.product-search');
            const productIdInput = row.querySelector('.product-id');
            const results = row.querySelector('.installment-product-results');
          const quantityInput = row.querySelector('.item-quantity');
          const lineTotalInput = row.querySelector('.line-total-input');
            let searchTimer;
            let searchController;

            function selectProduct(product) {
              productIdInput.value = product.id;
              productIdInput.dataset.price = product.price;
              searchInput.value = product.name;
              searchInput.setAttribute('aria-expanded', 'false');
              results.classList.add('d-none');
              lineTotalInput.value = formatPrice(product.price * parseInt(quantityInput.value || 0));
              updateInstallmentTotals();
            }

            function renderProductResults(products, query) {
              results.replaceChildren();
              if (!products.length) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-body-secondary';
                empty.textContent = `لا توجد منتجات مطابقة لـ «${query}»`;
                results.appendChild(empty);
              }
              products.forEach(product => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'list-group-item list-group-item-action d-flex justify-content-between gap-3';
                const name = document.createElement('span');
                name.textContent = product.name;
                const price = document.createElement('span');
                price.className = 'text-nowrap text-body-secondary';
                price.textContent = `${formatPrice(product.price)} ج.م`;
                option.append(name, price);
                let pointerSelectionHandled = false;
                option.addEventListener('pointerdown', event => {
                  if (event.button !== 0) return;
                  event.preventDefault();
                  pointerSelectionHandled = true;
                  selectProduct(product);
                });
                option.addEventListener('click', () => {
                  if (pointerSelectionHandled) {
                    pointerSelectionHandled = false;
                    return;
                  }
                  selectProduct(product);
                });
                results.appendChild(option);
              });
              results.classList.remove('d-none');
              searchInput.setAttribute('aria-expanded', 'true');
            }

            async function searchProducts(query) {
              searchController?.abort();
              searchController = new AbortController();
              try {
                const response = await fetch(`${installmentProductSearchUrl}?q=${encodeURIComponent(query)}`, {
                  headers: { 'Accept': 'application/json' },
                  credentials: 'same-origin',
                  signal: searchController.signal,
                });
                  if (!response.ok) throw new Error('تعذر البحث عن المنتجات.');
                const data = await response.json();
                renderProductResults(data.data || [], query);
              } catch (error) {
                if (error.name === 'AbortError') return;
                  results.replaceChildren();
                  const errorMessage = document.createElement('div');
                  errorMessage.className = 'list-group-item text-danger';
                  errorMessage.textContent = 'تعذر البحث عن المنتجات. تحقق من الاتصال وحاول مرة أخرى.';
                  results.appendChild(errorMessage);
                  results.classList.remove('d-none');
                  searchInput.setAttribute('aria-expanded', 'true');
              }
            }

            searchInput.addEventListener('input', () => {
              productIdInput.value = '';
              productIdInput.dataset.price = '0';
              lineTotalInput.value = '0.00';
              updateInstallmentTotals();
              clearTimeout(searchTimer);
              const query = searchInput.value.trim();
              if (query.length < 2) {
                searchController?.abort();
                results.classList.add('d-none');
                searchInput.setAttribute('aria-expanded', 'false');
                return;
              }
              searchTimer = setTimeout(() => searchProducts(query), 250);
          });

          quantityInput.addEventListener('input', () => {
              lineTotalInput.value = formatPrice(parseFloat(productIdInput.dataset.price || 0) * parseInt(quantityInput.value || 0));
              updateInstallmentTotals();
          });

            searchInput.addEventListener('keydown', event => {
              if (event.key === 'Escape') {
                results.classList.add('d-none');
                searchInput.setAttribute('aria-expanded', 'false');
              }
            });
            searchInput.addEventListener('blur', () => setTimeout(() => {
              results.classList.add('d-none');
              searchInput.setAttribute('aria-expanded', 'false');
            }, 120));

          row.querySelector('.remove-installment-item').addEventListener('click', () => {
              row.remove();
              if (!installmentItemsContainer.querySelector('.installment-item-row')) {
                  createInstallmentItemRow();
              }
              updateInstallmentTotals();
          });

          installmentItemsContainer.appendChild(row);
            if (item.product_id && !selectedProduct) {
              productIdInput.value = item.product_id;
              productIdInput.dataset.price = initialPrice;
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
