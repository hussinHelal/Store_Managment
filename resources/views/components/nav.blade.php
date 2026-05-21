<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand mx-2" href="{{ route('home') }}">
        <i class="fa-solid fa-blog"></i> {{ config('app.name', 'Bebo') }}
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Main Content -->
    <div class="collapse navbar-collapse" id="navbarNav">
        
        <!-- This will push everything to the left side -->
        <div class="navbar-nav me-auto d-flex align-items-center gap-3">   {{-- ms-auto + gap-3 --}}
            
            @guest
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('showLogin') }}">تسجيل الدخول</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('showRegister') }}">إنشاء حساب</a>
                </div>
            @endguest

            @auth
                <div class="dropdown" dir="rtl">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-2"
                       href="{{ route('profile.index') }}"
                       data-bs-toggle="dropdown"
                       role="button">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span class="d-none d-md-inline fw-medium">{{ Auth::user()->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user me-2"></i> الملف الشخصي
                            </a>
                        </li>
                        {{-- <li>
                            <a class="dropdown-item" href="{{ route('settings') }}">
                                <i class="fas fa-cog me-2"></i> الإعدادات
                            </a>
                        </li> --}}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </a>
                        </li>
                    </ul>
                </div>
            @endauth
            {{-- <div class="flex-grow-1"></div> --}}
            <div class="ms-auto">
                    <button id="theme-toggle" class="btn  d-flex align-items-center gap-2" type="button" aria-label="Toggle theme">
                        <i class="fas fa-moon fa-lg " id="theme-icon"></i>
                        {{-- <span class="d-none d-sm-inline theme-label">Dark Mode</span> --}}
                    </button>
                </div>
            </div>
        </div>
        
    </div>

  {{-- </div> --}}
</nav>
