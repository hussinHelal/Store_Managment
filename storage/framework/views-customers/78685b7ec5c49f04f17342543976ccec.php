<?php $__env->startSection('content'); ?>
    <span class="text-center border border-1 rounded text-bold">دفع قسط</span>
    <form action="<?php echo e(route('installments.pay', $installment)); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>


      <div class="mb-3">
        <label class="form-label">سعر المنتج</label>
        <input type="number" class="form-control" value="<?php echo e($installment->product_price); ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">إجمالي المدفوع سابقاً</label>
        <input type="number" class="form-control" value="<?php echo e($installment->paid_amount); ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">المتبقي الحالي</label>
        <input type="number" class="form-control" value="<?php echo e($installment->remaining); ?>" readonly>
      </div>

      <div class="mb-3">
        <label for="paid_amount" class="form-label">المبلغ المدفوع الآن</label>
        <input type="number" class="form-control" id="paid_amount" name="paid_amount"
               value="0" min="1" max="<?php echo e($installment->remaining); ?>">
      </div>

      <div class="mb-3">
        <label for="remaining_after" class="form-label">المتبقي بعد الدفع</label>
        <input type="number" class="form-control" id="remaining_after"
               value="<?php echo e($installment->remaining); ?>" readonly>
      </div>

      <button type="submit" class="btn btn-primary">دفع</button>
    </form>
    <div class="mt-3">
        <a href="<?php echo e(route('installments.index')); ?>" class="btn btn-danger">رجوع</a>
    </div>
    <?php $__env->startPush('scripts'); ?>
    <script>
      document.getElementById('paid_amount').addEventListener('input', function () {
          const currentRemaining = <?php echo e($installment->remaining); ?>;
          const payNow = parseFloat(this.value) || 0;
          const remainingAfter = currentRemaining - payNow;
          document.getElementById('remaining_after').value = remainingAfter;
      });
    </script>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\installments\pay.blade.php ENDPATH**/ ?>