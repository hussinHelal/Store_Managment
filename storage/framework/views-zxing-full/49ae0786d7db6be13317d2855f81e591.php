<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm">
  <div class="container-fluid d-flex align-items-center justify-content-between">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand d-flex align-items-center gap-2 ms-auto" href="<?php echo e(route('home')); ?>">
      <i class="fa-solid fa-mobile"></i>
      <span><?php echo e(config('app.name', 'PhoneStore')); ?></span>
    </a>

    <div class="collapse navbar-collapse m-1" id="navbarNav">
      <div class="navbar-nav align-items-center gap-3 me-auto">
        <?php if(auth()->guard()->guest()): ?>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('showLogin')); ?>">تسجيل الدخول</a>
          </div>
        <?php endif; ?>

        <?php if(auth()->guard()->check()): ?>
          <div class="dropdown me-3" dir="rtl">
            <a class="nav-link position-relative d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <i class="fas fa-bell fa-lg"></i>
              <?php if(isset($appNotifications) && $appNotifications->count()): ?>
                <?php $unreadCount = $unreadCount ?? $appNotifications->where('is_read', false)->count(); ?>
                <?php if($unreadCount > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                      <?php echo e($unreadCount); ?>

                      <span class="visually-hidden">إشعارات جديدة</span>
                  </span>
                <?php endif; ?>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if(isset($appNotifications) && $appNotifications->count()): ?>
                <?php $__currentLoopData = $appNotifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li>
                    <form method="POST" action="<?php echo e(route('notifications.markRead', $notification->id)); ?>">
                      <?php echo csrf_field(); ?>
                      <button class="dropdown-item text-start" type="submit">
                        <strong><?php echo e($notification->title); ?></strong>
                        <?php if(!$notification->is_read): ?>
                          <span class="badge bg-primary ms-2">جديد</span>
                        <?php endif; ?>
                        <br>
                        <span class="text-muted small"><?php echo e(\Illuminate\Support\Str::limit($notification->message, 50)); ?></span>
                      </button>
                    </form>
                  </li>
                  <?php if(!$loop->last): ?>
                    <li><hr class="dropdown-divider"></li>
                  <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <li><hr class="dropdown-divider"></li>
              <?php else: ?>
                <li><span class="dropdown-item text-muted">لا توجد إشعارات حالياً</span></li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li>
                <a class="dropdown-item" href="<?php echo e(route('notifications.list')); ?>">عرض كل الإشعارات</a>
              </li>
              <li>
                <form method="POST" action="<?php echo e(route('notifications.markAllRead')); ?>">
                  <?php echo csrf_field(); ?>
                  <button class="dropdown-item" type="submit">وضع الكل كمُقروء</button>
                </form>
              </li>
              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('page.notifications.manage')): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="<?php echo e(route('admin.notifications.index')); ?>">إدارة الإشعارات</a>
                </li>
              <?php endif; ?>
            </ul>
          </div>

          <div class="dropdown m-1" dir="rtl">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <i class="fas fa-user-circle fa-lg"></i>
              <span class="d-none d-md-inline fw-medium"><?php echo e(Auth::user()->name); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="<?php echo e(route('profile.index')); ?>">
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
          <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
            <?php echo csrf_field(); ?>
          </form>
        <?php endif; ?>
      </div>
    </div>


    <button id="theme-toggle" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-2 ms-2" type="button" aria-label="Toggle theme">
      <i class="fas fa-moon fa-lg" id="theme-icon"></i>
      <span class="d-none d-sm-inline theme-label">الوضع الداكن</span>
    </button>
  </div>
</nav>
<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views/components/nav.blade.php ENDPATH**/ ?>