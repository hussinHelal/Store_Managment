

<?php $__env->startSection('content'); ?>
<div class="container py-3" style="max-width: 58rem">
    <div class="mb-4">
        <a href="<?php echo e(route('roles.index')); ?>" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> Assign Roles</a>
        <h1 class="h3 mt-2"><?php echo e($role ? 'Edit role' : 'Create New Role'); ?></h1>
    </div>

    <form method="POST" action="<?php echo e($role ? route('roles.update', $role) : route('roles.store')); ?>" data-role-form>
        <?php echo csrf_field(); ?>
        <?php if($role): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>
        <div class="mb-4">
            <label for="name" class="form-label">Role name</label>
            <input id="name" name="name" value="<?php echo e(old('name', $role?->name)); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required maxlength="100" autocomplete="off">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <fieldset class="mb-4">
            <legend class="h5">Page permissions</legend>
            <p class="text-body-secondary small">Manage access also grants View. Assign Roles is reserved for the superadmin.</p>
            <div class="table-responsive border rounded">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th scope="col">Page</th><th scope="col" class="text-center">View</th><th scope="col" class="text-center">Manage</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $viewName = "page.{$page}.view";
                                $manageName = "page.{$page}.manage";
                                $viewChecked = old("permissions.{$page}.view", $permissions->has($viewName));
                                $manageChecked = old("permissions.{$page}.manage", $permissions->has($manageName));
                            ?>
                            <tr>
                                <th scope="row"><?php echo e($label); ?></th>
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="permissions[<?php echo e($page); ?>][view]" value="1" <?php if($viewChecked): echo 'checked'; endif; ?> aria-label="View <?php echo e($label); ?>"></td>
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="permissions[<?php echo e($page); ?>][manage]" value="1" <?php if($manageChecked): echo 'checked'; endif; ?> aria-label="Manage <?php echo e($label); ?>"></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php $__errorArgs = ['permissions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-2"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </fieldset>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?php echo e($role ? 'Save changes' : 'Create role'); ?></button>
            <a href="<?php echo e(route('roles.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.querySelectorAll('[data-role-form]').forEach((form) => {
        form.querySelectorAll('input[name$="[manage]"]').forEach((manage) => {
            manage.addEventListener('change', () => {
                if (manage.checked) {
                    const view = form.querySelector(`input[name="${manage.name.replace('[manage]', '[view]')}"]`);
                    if (view) view.checked = true;
                }
            });
        });
        form.addEventListener('submit', () => form.querySelectorAll('button[type="submit"]').forEach((button) => button.disabled = true));
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\roles\form.blade.php ENDPATH**/ ?>