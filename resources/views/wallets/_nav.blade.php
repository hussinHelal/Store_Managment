@php
    $walletTabs = [
        ['wallets.index', ['wallets.index', 'wallets.create', 'wallets.edit'], 'المحافظ', 'page.wallets.view', 'fa-wallet'],
        ['wallet_cashier.index', ['wallet_cashier.index'], 'الكاشير', 'page.wallet_cashier.view', 'fa-cash-register'],
        ['wallet_cashier.debts', ['wallet_cashier.debts'], 'الآجل', 'page.wallet_cashier.view', 'fa-hand-holding-dollar'],
        ['wallets.expenses.index', ['wallets.expenses.*'], 'المصروفات والخزينة', 'page.wallets.view', 'fa-money-bill-transfer'],
        ['wallets.reports.index', ['wallets.reports.*'], 'التقارير', 'page.wallets.view', 'fa-chart-line'],
    ];
@endphp
<ul class="nav nav-pills flex-wrap gap-1 mb-3 d-print-none" aria-label="أقسام المحافظ">
    @foreach ($walletTabs as [$route, $patterns, $label, $ability, $icon])
        @can($ability)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs(...$patterns) ? 'active' : '' }}" href="{{ route($route) }}">
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                </a>
            </li>
        @endcan
    @endforeach
    @if (auth()->user()?->isSuperAdmin())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('wallets.audit.*') ? 'active' : '' }}" href="{{ route('wallets.audit.index') }}">
                <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> سجل العمليات
            </a>
        </li>
    @endif
</ul>
