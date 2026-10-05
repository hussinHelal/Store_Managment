

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 52rem">
	<div class="mb-4">
		<a href="<?php echo e(route('customers.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> العملاء</a>
		<h1 class="h3 mt-2">بيانات العميل</h1>
	</div>
	<dl class="row border-top pt-3">
		<dt class="col-sm-3">الاسم</dt><dd class="col-sm-9"><?php echo e($customer->name); ?></dd>
		<dt class="col-sm-3">الهاتف</dt><dd class="col-sm-9"><?php echo e($customer->phone); ?></dd>
		<dt class="col-sm-3">العنوان</dt><dd class="col-sm-9"><?php echo e($customer->address); ?></dd>
	</dl>
	<a href="<?php echo e(route('customers.edit', $customer)); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/customers/show.blade.php ENDPATH**/ ?>