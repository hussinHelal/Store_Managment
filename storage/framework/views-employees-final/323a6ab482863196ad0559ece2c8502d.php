<?php
    $sidebarLinks = [
        ['route' => 'home',               'active' => 'home',            'can' => 'page.dashboard.view',    'icon' => 'fa-house',             'label' => 'الرئيسية'],
         ['route' => 'cashier.index',     'active' => 'cashier.*',       'can' => 'create-invoice',         'icon' => 'fa-cash-register',     'label' => 'الكاشير'],
        ['route' => 'categories.index',   'active' => 'categories.*',    'can' => 'page.categories.view',   'icon' => 'fa-list',              'label' => 'التصنيفات'],
        ['route' => 'products.index',     'active' => 'products.*',      'can' => 'page.products.view',     'icon' => 'fa-cart-arrow-down',   'label' => 'المنتجات'],
        ['route' => 'maintenance.index',  'active' => 'maintenance.*',   'can' => 'page.maintenance.view',  'icon' => 'fa-wrench',            'label' => 'الصيانة'],
        ['route' => 'sales.index',        'active' => 'sales.*',         'can' => 'page.sales.view',        'icon' => 'fa-money-bill-wave',   'label' => 'المبيعات'],
        ['route' => 'invoices.index',     'active' => 'invoices.*',      'can' => 'page.invoices.view',     'icon' => 'fa-file-invoice',      'label' => 'الفواتير'],
        ['route' => 'customers.index',    'active' => 'customers.*',     'can' => 'page.customers.view',    'icon' => 'fa-users',             'label' => 'العملاء'],
        ['route' => 'suppliers.index',    'active' => 'suppliers.*',     'can' => 'page.suppliers.view',    'icon' => 'fa-truck-field',       'label' => 'الموردون والحسابات الدائنة'],
        ['route' => 'installments.index', 'active' => 'installments.*',  'can' => 'page.installments.view', 'icon' => 'fa-credit-card',       'label' => 'الديون'],
        ['route' => 'users.index',        'active' => 'users.*',         'can' => 'page.staff.view',        'icon' => 'fa-user-group',        'label' => 'الموظفون'],
    ];
?>

<aside class="sidebar bg-body-tertiary border-start p-3 flex-shrink-0">
    <nav class="nav flex-column gap-1" aria-label="القائمة الرئيسية">

        <?php $__currentLoopData = $sidebarLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <?php if(\Illuminate\Support\Facades\Route::has($link['route'])): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check($link['can'])): ?>
                    <?php $isActive = request()->routeIs($link['active']); ?>
                    <a href="<?php echo e(route($link['route'])); ?>"
                       class="nav-link rounded d-flex align-items-center gap-2 <?php echo e($isActive ? 'active' : 'text-body'); ?>"
                       <?php if($isActive): ?> aria-current="page" <?php endif; ?>>
                        <i class="fa-solid <?php echo e($link['icon']); ?>" aria-hidden="true"></i>
                        <span><?php echo e($link['label']); ?></span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if(auth()->check() && auth()->user()->isSuperAdmin() && \Illuminate\Support\Facades\Route::has('roles.index')): ?>
            <?php $rolesActive = request()->routeIs('roles.*'); ?>
            <a href="<?php echo e(route('roles.index')); ?>"
               class="nav-link rounded d-flex align-items-center gap-2 <?php echo e($rolesActive ? 'active' : 'text-body'); ?>"
               <?php if($rolesActive): ?> aria-current="page" <?php endif; ?>>
                <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                <span>تعيين الأدوار</span>
            </a>
        <?php endif; ?>

    </nav>
</aside><?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/components/sidebar.blade.php ENDPATH**/ ?>