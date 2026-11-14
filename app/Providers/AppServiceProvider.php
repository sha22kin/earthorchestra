<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        RateLimiter::for('arrangements', fn (Request $request) => [
            Limit::perMinute(6)->by($request->ip()),
            Limit::perDay(60)->by($request->ip()),
        ]);

        Gate::define('manage-users', fn (User $user) => $user->isAdmin());

        Paginator::defaultView('admin.partials.pagination');
    }
}
