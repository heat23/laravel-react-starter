<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // We register our own billing routes in routes/web.php
        Cashier::ignoreRoutes();
        Cashier::useSubscriptionModel(Subscription::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('viewHorizon', fn (User $user) => $user->is_admin);

        RateLimiter::for('webhook-test', function ($request) {
            return Limit::perMinute(5)->by('webhook-test|'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('consent-store', function ($request) {
            return Limit::perMinute(10)->by('consent-store|'.$request->ip());
        });

        Model::preventLazyLoading();

        // In production, log violations instead of throwing to avoid 500s
        // from undiscovered violations. In dev/test, exceptions surface immediately.
        if (app()->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                Log::warning('Lazy loading violation', [
                    'model' => get_class($model),
                    'relation' => $relation,
                ]);
            });
        }

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
