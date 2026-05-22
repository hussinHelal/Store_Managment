<aside style="width: 250px; min-height: calc(100vh - 56px);" class="bg-body-tertiary border-start p-3 flex-shrink-0">
    <nav class="nav flex-column gap-1">

        <a href="{{ route('home') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('home') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-house"></i>
            <span>الرئيسية</span>
        </a>

        <a href="{{ route('categories.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('categories.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-list"></i>
            <span>التصنيفات</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('products.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-cart-arrow-down"></i>
            <span>المنتجات</span>
        </a>
        <a href="{{ route('maintenance.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('maintenance.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-wrench"></i>
            <span>الصيانة</span>
        </a>

        <a href="{{ route('sales.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('sales.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-money-bill-wave"></i>
            <span>المبيعات</span>
        </a>

        <a href="{{ route('invoices.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('invoices.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-file-invoice"></i>
            <span>الفواتير</span>
        </a>

        <a href="{{ route('customers.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('customers.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-users"></i>
            <span>العملاء</span>
        </a>

        <a href="{{ route('installments.index') }}"
           class="nav-link rounded d-flex align-items-center gap-2 {{ request()->routeIs('installments.*') ? 'active bg-primary text-white' : 'text-body' }}">
            <i class="fa-solid fa-credit-card"></i>
            <span>الديون</span>
        </a>

    </nav>
</aside>