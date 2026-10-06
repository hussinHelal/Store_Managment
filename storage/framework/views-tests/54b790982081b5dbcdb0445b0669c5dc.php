

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الموظفون</h1>
            <p class="text-body-secondary mb-0">إدارة حسابات الموظفين ومراجعة الأدوار وحالة الوصول.</p>
        </div>
        <?php if(auth()->user()->isSuperAdmin()): ?>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary" href="<?php echo e(route('roles.index')); ?>">
                    <i class="fa-solid fa-user-shield ms-1" aria-hidden="true"></i> إدارة الأدوار
                </a>
                <a class="btn btn-primary" href="<?php echo e(route('users.create')); ?>">
                    <i class="fa-solid fa-user-plus ms-1" aria-hidden="true"></i> إضافة موظف جديد
                </a>
            </div>
        <?php endif; ?>
    </div>

    <form method="GET" action="<?php echo e(route('users.index')); ?>" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8 col-lg-5">
            <label for="staff-search" class="form-label">بحث باسم المستخدم أو الاسم أو البريد الإلكتروني</label>
            <input id="staff-search" class="form-control" type="search" name="search" value="<?php echo e($search); ?>" maxlength="80">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass ms-1" aria-hidden="true"></i> بحث</button>
            <?php if($search !== ''): ?>
                <a class="btn btn-link" href="<?php echo e(route('users.index')); ?>">مسح البحث</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if($users->isEmpty()): ?>
        <div class="border rounded p-4 text-center text-body-secondary">
            <i class="fa-solid fa-users fs-3 mb-2" aria-hidden="true"></i>
            <p class="mb-0">لا توجد حسابات موظفين تطابق البحث.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">اسم المستخدم</th>
                        <th scope="col">الاسم</th>
                        <th scope="col">البريد الإلكتروني</th>
                        <th scope="col">الدور</th>
                        <th scope="col">الحالة</th>
                        <?php if(auth()->user()->isSuperAdmin()): ?>
                            <th scope="col" class="text-end">الإجراءات</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($user->username); ?></td>
                            <td><?php echo e($user->name); ?></td>
                            <td dir="ltr" class="text-end"><?php echo e($user->email); ?></td>
                            <td><?php echo e($user->roles->pluck('name')->map(fn ($role) => ['Admin' => 'مدير النظام', 'Cashier' => 'كاشير', 'Inventory Manager' => 'مدير المخزون', 'Sales Manager' => 'مدير المبيعات'][$role] ?? $role)->join('، ') ?: 'غير محدد'); ?></td>
                            <td>
                                <span class="badge <?php echo e($user->is_active ? 'text-bg-success' : 'text-bg-secondary'); ?>">
                                    <?php echo e($user->is_active ? 'نشط' : 'غير نشط'); ?>

                                </span>
                            </td>
                            <?php if(auth()->user()->isSuperAdmin()): ?>
                                <td class="text-end text-nowrap">
                                    <?php if(!$user->system_account && !$user->isSuperAdmin()): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('users.edit', $user)); ?>" aria-label="تعديل <?php echo e($user->username); ?>">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-user-<?php echo e($user->id); ?>" aria-label="حذف <?php echo e($user->username); ?>">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                        <div class="modal fade" id="delete-user-<?php echo e($user->id); ?>" tabindex="-1" aria-labelledby="delete-user-title-<?php echo e($user->id); ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h2 class="modal-title fs-5" id="delete-user-title-<?php echo e($user->id); ?>">حذف حساب الموظف؟</h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                    </div>
                                                    <div class="modal-body">سيؤدي هذا إلى حذف الحساب <?php echo e($user->username); ?> وإلغاء صلاحية الوصول نهائيًا.</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <form method="POST" action="<?php echo e(route('users.destroy', $user)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button class="btn btn-danger" type="submit">حذف الحساب</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-body-secondary small">حساب محمي</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($users->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\users\index.blade.php ENDPATH**/ ?>