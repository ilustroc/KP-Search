<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Model::preventLazyLoading(! app()->isProduction());

        Gate::before(function (User $user, string $ability): ?bool {
            if (! str_starts_with($ability, 'search.')) {
                return null;
            }

            return $user->is_active && $user->hasPermission($ability);
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::transliterate(Str::lower($request->string('email')->toString()));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('search', function (Request $request): Limit {
            return Limit::perMinute(30)->by(($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip());
        });
    }
}
