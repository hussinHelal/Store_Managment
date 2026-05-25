<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\ProductsPolicy;
use App\Policies\UserPolicy;
use App\Policies\CustomersPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\InstallmentsPolicy;
use App\Policies\ProfilePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use App\Models\AppNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            // set default locale to Arabic for validation/messages
            app()->setLocale('ar');
         Paginator::useBootstrap();

        RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(30)->by(
            $request->user()?->id ?: $request->ip()
        );
        });


        Gate::define('create-category',[CategoryPolicy::class,'store']);
        Gate::define('update-category',[CategoryPolicy::class,'update']);
        Gate::define('delete-category',[CategoryPolicy::class,'destroy']);
        Gate::define('view-category',[CategoryPolicy::class,'view']);

        Gate::define('create-customers',[CustomersPolicy::class,'store']);
        Gate::define('update-customers',[CustomersPolicy::class,'update']);
        Gate::define('delete-customers',[CustomersPolicy::class,'destroy']);
        Gate::define('view-customers',[CustomersPolicy::class,'view']);

        Gate::define('create-products',[ProductsPolicy::class,'store']);
        Gate::define('update-products',[ProductsPolicy::class,'update']);
        Gate::define('delete-products',[ProductsPolicy::class,'destroy']);
        Gate::define('view-products',[ProductsPolicy::class,'view']);

        Gate::define('create-user',[UserPolicy::class,'store']);
        Gate::define('update-user',[UserPolicy::class,'update']);
        Gate::define('delete-user',[UserPolicy::class,'destroy']);
        Gate::define('view-user',[UserPolicy::class,'view']);

        Gate::define('modify-user-role', function(User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('create-invoice',[InvoicePolicy::class,'store']);
        Gate::define('update-invoice',[InvoicePolicy::class,'update']);
        Gate::define('delete-invoice',[InvoicePolicy::class,'destroy']);
        Gate::define('refund-invoice',[InvoicePolicy::class,'refund']);
        Gate::define('view-invoice',[InvoicePolicy::class,'view']);

        Gate::define('create-installments',[InstallmentsPolicy::class,'store']);
        Gate::define('update-installments',[InstallmentsPolicy::class,'update']);
        Gate::define('delete-installments',[InstallmentsPolicy::class,'destroy']);
        Gate::define('view-installments',[InstallmentsPolicy::class,'view']);

        Gate::define('create-profile',[ProfilePolicy::class,'store']);
        Gate::define('update-profile',[ProfilePolicy::class,'update']);
        Gate::define('delete-profile',[ProfilePolicy::class,'destroy']);
        Gate::define('view-profile',[ProfilePolicy::class,'view']);

        View::composer('components.nav', function ($view) {
            if (! Auth::check()) {
                $view->with('appNotifications', collect());
                return;
            }

            $user = Auth::user();
            $notifications = AppNotification::active()->latest()->get();
            $reads = \App\Models\NotificationRead::where('user_id', $user->id)->pluck('app_notification_id')->toArray();

            // annotate notifications with is_read for the current user
            $notifications->transform(function ($n) use ($reads) {
                $n->is_read = in_array($n->id, $reads);
                return $n;
            });

            $view->with('appNotifications', $notifications);
        });
    }
}
