@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">انشاء منتج</span>
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name">
      </div>

      <div class="mb-3">
        <label for="price" class="form-label">السعر</label>
        <input type="number" class="form-control" id="price" name="price">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
      </div>

      <div class="mb-3">
        <label for="notes" class="form-label">ملاحظات</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label for="barcode" class="form-label">باركود المنتج</label>
        <div class="input-group">
            <input type="text" class="form-control" id="barcode" name="barcode" placeholder="استخدم جهاز الماسح أو الكاميرا" autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="scan-barcode-btn">مسح بالكاميرا</button>
        </div>
        <div id="barcode-scanner" class="mt-2 d-none">
            <video id="barcode-video" class="w-100 border rounded" style="max-height: 320px;"></video>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-danger" id="stop-scan-btn">إيقاف المسح</button>
            </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="category_id" class="form-label">الصنف</label>
        <select class="form-select" dir="ltr" id="category_id" name="category_id" >
          <option value="" >اختر صنف</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}">
                {{ $category->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="stock" class="form-label">المخزون</label>
        <input type="number" class="form-control" id="stock" name="stock">
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">صورة المنتج</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
      </div>

      @include('components.form-actions', ['submitLabel' => 'انشاء', 'backUrl' => route('products.index')])
    </form>

    <script>
        const scanBtn = document.getElementById('scan-barcode-btn');
        const stopBtn = document.getElementById('stop-scan-btn');
        const scanner = document.getElementById('barcode-scanner');
        const video = document.getElementById('barcode-video');
        const barcodeInput = document.getElementById('barcode');
      let scannerSession = null;
        let scanning = false;

      function startScanner() {
        if (!window.createBarcodeScanner) {
          window.showBootstrapAlert('قارئ الباركود غير متاح. حدّث الصفحة وحاول مرة أخرى.', 'danger');
          return;
        }
        scanner.classList.remove('d-none');
        scanBtn.disabled = true;
        scanning = true;
        scannerSession = window.createBarcodeScanner(video, (barcode) => {
          barcodeInput.value = barcode;
          stopScanner();
        }, (error) => {
          stopScanner();
          window.showBootstrapAlert('تعذر الوصول إلى الكاميرا: ' + error.message, 'danger');
        });
        }

        function stopScanner() {
            scanning = false;
        scannerSession?.stop();
        scannerSession = null;
            scanner.classList.add('d-none');
        scanBtn.disabled = false;
        }

        scanBtn?.addEventListener('click', startScanner);
        stopBtn?.addEventListener('click', stopScanner);
    </script>
@endsection
