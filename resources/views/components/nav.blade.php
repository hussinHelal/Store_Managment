<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm">
  <div class="container-fluid d-flex align-items-center justify-content-between">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand d-flex align-items-center gap-2 ms-auto" href="{{ route('home') }}">
      <i class="fa-solid fa-mobile"></i>
      <span>{{ config('app.name', 'PhoneStore') }}</span>
    </a>

    <div class="collapse navbar-collapse m-1" id="navbarNav">
      <div class="navbar-nav align-items-center gap-3 me-auto">
        @guest
          <div class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('showLogin') }}">تسجيل الدخول</a>
          </div>
        @endguest

        @auth
          <div class="dropdown me-3" dir="rtl">
            <a class="nav-link position-relative d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <i class="fas fa-bell fa-lg"></i>
              @if(isset($appNotifications) && $appNotifications->count())
                @php $unreadCount = $unreadCount ?? $appNotifications->where('is_read', false)->count(); @endphp
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" data-notification-count>
                      <span data-count-value>{{ $unreadCount }}</span>
                      <span class="visually-hidden">إشعارات جديدة</span>
                  </span>
                @endif
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              @if(isset($appNotifications) && $appNotifications->count())
                @foreach($appNotifications->take(5) as $notification)
                  <li>
                      <button class="dropdown-item text-start {{ $notification->is_read ? 'text-muted' : '' }}" type="button" data-notification-read data-notification-id="{{ $notification->id }}" data-read-url="{{ route('notifications.markRead', $notification->id) }}" aria-pressed="{{ $notification->is_read ? 'true' : 'false' }}">
                        <strong>{{ $notification->title }}</strong>
                        @if(!$notification->is_read)
                          <span class="badge bg-primary ms-2">جديد</span>
                        @endif
                        <br>
                        <span class="text-muted small">{{ \Illuminate\Support\Str::limit($notification->message, 50) }}</span>
                      </button>
                  </li>
                  @if(!$loop->last)
                    <li><hr class="dropdown-divider"></li>
                  @endif
                @endforeach
                <li><hr class="dropdown-divider"></li>
              @else
                <li><span class="dropdown-item text-muted">لا توجد إشعارات حالياً</span></li>
                <li><hr class="dropdown-divider"></li>
              @endif
              <li>
                <a class="dropdown-item" href="{{ route('notifications.list') }}">عرض كل الإشعارات</a>
              </li>
              <li>
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                  @csrf
                  <button class="dropdown-item" type="submit">وضع الكل كمُقروء</button>
                </form>
              </li>
              @can('page.notifications.manage')
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="{{ route('admin.notifications.index') }}">إدارة الإشعارات</a>
                </li>
              @endcan
            </ul>
          </div>

          <div class="dropdown m-1" dir="rtl">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <i class="fas fa-user-circle fa-lg"></i>
              <span class="d-none d-md-inline fw-medium">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ route('profile.index') }}">
                  <i class="fas fa-user me-2"></i> الملف الشخصي
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                </a>
              </li>
            </ul>
          </div>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        @endauth
      </div>
    </div>


    <button id="theme-toggle" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-2 ms-2" type="button" aria-label="Toggle theme">
      <i class="fas fa-moon fa-lg" id="theme-icon"></i>
      <span class="d-none d-sm-inline theme-label">الوضع الداكن</span>
    </button>
  </div>
</nav>
@push('scripts')
<script>
document.querySelectorAll('[data-notification-read]').forEach((notificationButton) => {
  notificationButton.addEventListener('click', async () => {
    if (notificationButton.getAttribute('aria-pressed') === 'true') return;
    notificationButton.disabled = true;
    try {
      const response = await fetch(notificationButton.dataset.readUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': @json(csrf_token()),
        },
      });
      if (!response.ok) throw new Error();

      const matchingButtons = [...document.querySelectorAll('[data-notification-id]')]
        .filter((button) => button.dataset.notificationId === notificationButton.dataset.notificationId);
      const wasUnread = matchingButtons.some((button) => button.getAttribute('aria-pressed') !== 'true');
      matchingButtons.forEach((button) => {
        button.classList.add('text-muted');
        button.setAttribute('aria-pressed', 'true');
        button.querySelector('.badge')?.remove();
      });

      if (wasUnread) {
        const count = document.querySelector('[data-notification-count]');
        const countValue = count?.querySelector('[data-count-value]');
        const remaining = Math.max(0, Number(countValue?.textContent || 0) - 1);
        if (countValue) countValue.textContent = String(remaining);
        if (remaining === 0) count?.remove();
      }
    } catch (error) {
      window.showBootstrapAlert('تعذر وضع الإشعار كمقروء. حاول مرة أخرى.', 'danger');
    } finally {
      notificationButton.disabled = false;
    }
  });
});
</script>
@endpush
