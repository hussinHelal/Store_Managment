@php
    $sidebarGroups = [
        ['key' => 'overview', 'label' => 'الرئيسية', 'links' => [
            ['route' => 'home', 'active' => 'home', 'can' => 'page.dashboard.view', 'icon' => 'fa-house', 'label' => 'لوحة التحكم'],
        ]],
        ['key' => 'sales', 'label' => 'البيع', 'links' => [
            ['route' => 'cashier.index', 'active' => 'cashier.*', 'can' => 'create-invoice', 'icon' => 'fa-cash-register', 'label' => 'الكاشير'],
            ['route' => 'sales.index', 'active' => 'sales.*', 'can' => 'page.sales.view', 'icon' => 'fa-money-bill-wave', 'label' => 'المبيعات'],
            ['route' => 'invoices.index', 'active' => 'invoices.*', 'can' => 'page.invoices.view', 'icon' => 'fa-file-invoice', 'label' => 'الفواتير'],
        ]],
        ['key' => 'inventory', 'label' => 'المخزون', 'links' => [
            ['route' => 'used_phones.index', 'active' => 'used_phones.*', 'can' => 'page.used_phones.view', 'icon' => 'fa-mobile-screen-button', 'label' => 'الموبايلات المستعملة'],
            ['route' => 'branch_stock.index', 'active' => 'branch_stock.index', 'can' => 'page.branch_stock.view', 'icon' => 'fa-warehouse', 'label' => 'مخزون الفروع'],
            ['route' => 'stock_movements.index', 'active' => 'stock_movements.*', 'can' => 'page.stock_movements.view', 'icon' => 'fa-arrow-right-arrow-left', 'label' => 'حركات المخزون'],
            ['route' => 'branch_stock.transfers.index', 'active' => 'branch_stock.transfers.*', 'can' => 'page.branch_stock.view', 'icon' => 'fa-truck-ramp-box', 'label' => 'تحويلات المخزون'],
            ['route' => 'stocktake.index', 'active' => 'stocktake.*', 'can' => 'page.stocktake.view', 'icon' => 'fa-clipboard-check', 'label' => 'جرد المخزون'],
            ['route' => 'products.index', 'active' => 'products.*', 'can' => 'page.products.view', 'icon' => 'fa-cart-arrow-down', 'label' => 'المنتجات'],
            ['route' => 'categories.index', 'active' => 'categories.*', 'can' => 'page.categories.view', 'icon' => 'fa-list', 'label' => 'التصنيفات'],
            ['route' => 'damaged.index', 'active' => 'damaged.*', 'can' => 'page.damaged.view', 'icon' => 'fa-triangle-exclamation', 'label' => 'الهالك'],
        ]],
        ['key' => 'service', 'label' => 'العملاء والخدمة', 'links' => [
            ['route' => 'installations.index', 'active' => 'installations.*', 'can' => 'page.installations.view', 'icon' => 'fa-calendar-check', 'label' => 'مواعيد التركيب'],
            ['route' => 'customers.index', 'active' => 'customers.*', 'can' => 'page.customers.view', 'icon' => 'fa-users', 'label' => 'العملاء'],
            ['route' => 'devices.index', 'active' => 'devices.*', 'can' => 'page.devices.view', 'icon' => 'fa-mobile-screen', 'label' => 'الأجهزة المتتبعة'],
            ['route' => 'maintenance.index', 'active' => 'maintenance.*', 'can' => 'page.maintenance.view', 'icon' => 'fa-wrench', 'label' => 'الصيانة'],
        ]],
        ['key' => 'accounts', 'label' => 'الحسابات', 'links' => [
            ['route' => 'wallets.index', 'active' => 'wallets.*', 'can' => 'page.wallets.view', 'icon' => 'fa-wallet', 'label' => 'المحافظ'],
            ['route' => 'wallet_cashier.index', 'active' => 'wallet_cashier.*', 'can' => 'page.wallet_cashier.view', 'icon' => 'fa-money-bill-transfer', 'label' => 'كاشير المحافظ'],
            ['route' => 'suppliers.index', 'active' => 'suppliers.*', 'can' => 'page.suppliers.view', 'icon' => 'fa-truck-field', 'label' => 'الموردون وأرصدتهم'],
            ['route' => 'installments.index', 'active' => 'installments.*', 'can' => 'page.installments.view', 'icon' => 'fa-credit-card', 'label' => 'الديون والأقساط'],
            ['route' => 'sales_reports.index', 'active' => 'sales_reports.*', 'can' => 'page.sales_reports.view', 'icon' => 'fa-chart-line', 'label' => 'تقارير المبيعات'],
        ]],
        ['key' => 'administration', 'label' => 'الإدارة', 'links' => [
            ['route' => 'diagnostics.index', 'active' => 'diagnostics.*', 'can' => 'page.diagnostics.view', 'icon' => 'fa-stethoscope', 'label' => 'تشخيص الأجهزة'],
            ['route' => 'users.index', 'active' => 'users.*', 'can' => 'page.staff.view', 'icon' => 'fa-user-group', 'label' => 'الموظفون'],
            ['route' => 'backup.index', 'active' => 'backup.*', 'can' => 'page.backup.view', 'icon' => 'fa-database', 'label' => 'النسخ الاحتياطي'],
        ]],
    ];
@endphp

<aside id="appSidebar" class="sidebar offcanvas-lg offcanvas-start bg-body-tertiary border-start p-3 flex-shrink-0" tabindex="-1" aria-labelledby="appSidebarLabel">
    <div class="offcanvas-header d-lg-none px-0 pt-0">
        <h2 class="offcanvas-title fs-5" id="appSidebarLabel">القائمة</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
    </div>
    <nav class="nav flex-column gap-1" aria-label="القائمة الرئيسية">
        @foreach ($sidebarGroups as $group)
            @php
                $visibleLinks = array_values(array_filter($group['links'], fn (array $link): bool => app('router')->has($link['route']) && auth()->check() && auth()->user()->can($link['can'])));
            @endphp
            @if (count($visibleLinks))
                @php $groupIsActive = collect($visibleLinks)->contains(fn (array $link): bool => request()->routeIs($link['active'])); @endphp
                <details class="sidebar-group" data-sidebar-group="{{ $group['key'] }}" @if ($groupIsActive) open @endif>
                    <summary class="sidebar-group-toggle" aria-label="{{ $group['label'] }}">
                        <span>{{ $group['label'] }}</span><i class="fa-solid fa-chevron-down sidebar-group-chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="sidebar-group-links">
                        @foreach ($visibleLinks as $link)
                            @php $isActive = request()->routeIs($link['active']); @endphp
                            <a href="{{ route($link['route']) }}" class="nav-link rounded d-flex align-items-center gap-2 {{ $isActive ? 'active' : 'text-body' }}" @if ($isActive) aria-current="page" @endif>
                                <i class="fa-solid {{ $link['icon'] }}" aria-hidden="true"></i><span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach
        @if (auth()->check() && auth()->user()->isSuperAdmin() && app('router')->has('roles.index'))
            <h2 class="small fw-semibold text-secondary px-2 mt-3 mb-1">الإدارة</h2>
            @php $rolesActive = request()->routeIs('roles.*'); @endphp
            <a href="{{ route('roles.index') }}" class="nav-link rounded d-flex align-items-center gap-2 {{ $rolesActive ? 'active' : 'text-body' }}" @if ($rolesActive) aria-current="page" @endif>
                <i class="fa-solid fa-user-shield" aria-hidden="true"></i><span>تعيين الأدوار</span>
            </a>
        @endif
    </nav>
</aside>
