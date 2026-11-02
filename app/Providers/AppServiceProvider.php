<?php

namespace App\Providers;

use App\Models\User;
use App\Support\BrandContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(BrandContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super admins pass every ability; returning null lets normal checks run for everyone else.
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);

        // Any ability without an explicit gate or policy is treated as a permission slug, e.g. Gate::allows('brands.create').
        Gate::after(fn (User $user, string $ability, ?bool $result) => $result ?? $user->hasPermission($ability));

        View::composer('layouts.app', function ($view) {
            $context = app(BrandContext::class);

            $view->with([
                'brands' => $context->available(),
                'currentBrand' => $context->current(),
            ]);
        });
    }
}
