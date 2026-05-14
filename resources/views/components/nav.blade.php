<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container-fluid">

    <a class="navbar-brand mx-1" href="{{ route('home') }}">
        <i class="fa-solid fa-blog"></i> {{config('app.name','Bebo')}}
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="navbar-nav me-auto d-flex align-items-center gap-2">


        @guest
            <div class="d-flex gap-2 me-auto">
                <a class="btn btn-outline-primary btn-sm" href="{{ route('showLogin') }}">Login</a>
                <a class="btn btn-primary btn-sm" href="{{ route('showRegister') }}">Sign Up</a>
            </div>
        @endguest

        @auth
            <div class="dropdown d-flex gap-2 me-auto">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="fas fa-user me-2"></i> الملف الشخصي
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('settings') }}">
                            <i class="fas fa-cog me-2"></i> الإعدادات
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                        </a>
                    </li>
                </ul>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        @endauth
        
        <form class="d-flex" role="search">
            <input class="form-control" type="search" placeholder="بحث..."/>
            <button class="btn btn-outline-secondary me-2" type="submit">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>

      </div>
      </div>
    </div>

  </div>
</nav>