

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Assign Roles</h1>
            <p class="text-body-secondary mb-0">Manage role access and assign roles to staff.</p>
        </div>
        <a class="btn btn-primary" href="<?php echo e(route('roles.create')); ?>">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Create New Role
        </a>
    </div>

    <section class="mb-5" aria-labelledby="role-list-title">
        <h2 id="role-list-title" class="h5 mb-3">Roles</h2>
        <?php if($roles->isEmpty()): ?>
            <div class="border rounded p-4 text-center text-body-secondary">No roles are configured.</div>
        <?php else: ?>
            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col">Role</th><th scope="col">Assigned users</th><th scope="col" class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($role->name); ?></td>
                                <td><?php echo e($role->users_count); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php if(strtolower($role->name) !== 'superadmin'): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('roles.edit', $role)); ?>" aria-label="Edit <?php echo e($role->name); ?>">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-body-secondary small">Protected role</span>
                                    <?php endif; ?>
                                    <?php if(!in_array(strtolower($role->name), ['admin', 'superadmin'], true) && $role->users_count === 0): ?>
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-role-<?php echo e($role->id); ?>" aria-label="Delete <?php echo e($role->name); ?>">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                        <div class="modal fade" id="delete-role-<?php echo e($role->id); ?>" tabindex="-1" aria-labelledby="delete-role-title-<?php echo e($role->id); ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h2 class="modal-title fs-5" id="delete-role-title-<?php echo e($role->id); ?>">Delete role?</h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">The <?php echo e($role->name); ?> role will be permanently deleted.</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="POST" action="<?php echo e(route('roles.destroy', $role)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button class="btn btn-danger" type="submit">Delete role</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section aria-labelledby="assignment-title">
        <h2 id="assignment-title" class="h5 mb-3">Staff role assignments</h2>
        <?php if($users->isEmpty()): ?>
            <div class="border rounded p-4 text-center text-body-secondary">No staff accounts are available.</div>
        <?php else: ?>
            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col">Username</th><th scope="col">Name</th><th scope="col">Current role</th><th scope="col">Status</th><th scope="col" class="text-end">Assign role</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($user->username); ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->roles->pluck('name')->join(', ') ?: 'Unassigned'); ?></td>
                                <td><span class="badge <?php echo e($user->is_active ? 'text-bg-success' : 'text-bg-secondary'); ?>"><?php echo e($user->is_active ? 'Active' : 'Inactive'); ?></span></td>
                                <td class="text-end">
                                    <?php if(!$user->system_account && !$user->isSuperAdmin()): ?>
                                        <form method="POST" action="<?php echo e(route('roles.users.assign', $user)); ?>" class="d-flex justify-content-end gap-2">
                                            <?php echo csrf_field(); ?>
                                            <select name="role_id" class="form-select form-select-sm" aria-label="Role for <?php echo e($user->username); ?>" required>
                                                <?php $__currentLoopData = $assignableRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($role->id); ?>" <?php if($user->roles->contains('id', $role->id)): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-body-secondary small">Protected account</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($users->links()); ?></div>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/roles/index.blade.php ENDPATH**/ ?>