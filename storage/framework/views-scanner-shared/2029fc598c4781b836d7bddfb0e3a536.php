<?php $__env->startSection('content'); ?>

    <span class="text-center border border-1 rounded text-bold">تحديث منتج</span>
    <form action="<?php echo e(route('products.update', $product->id)); ?>" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($product->name); ?>">
      </div>

      <div class="mb-3">
        <label for="price" class="form-label">السعر</label>
        <input type="number" class="form-control" id="price" name="price" value="<?php echo e($product->price); ?>">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <input type="text" class="form-control" id="description" name="description" value="<?php echo e($product->description); ?>">
      </div>

      <div class="mb-3">
        <label for="barcode" class="form-label">باركود المنتج</label>
        <div class="input-group">
            <input type="text" class="form-control" id="barcode" name="barcode" placeholder="استخدم جهاز الماسح أو الكاميرا" autocomplete="off" value="<?php echo e($product->barcode); ?>">
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
          <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->id); ?>" <?php echo e($category->id == $product->category_id ? 'selected' : ''); ?>>
                <?php echo e($category->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="stock" class="form-label">المخزون</label>
        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo e($product->stock); ?>">
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">صورة المنتج</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
      </div>

      <?php if($product->image): ?>
      <div class="mb-3">
          <label class="form-label">الصورة الحالية</label>
          <div>
              <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>" class="img-fluid rounded" style="max-width: 180px; max-height: 180px; object-fit: cover;">
          </div>
      </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

     <div class="mt-3">
        <a href="<?php echo e(route('products.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/products/edit.blade.php ENDPATH**/ ?>