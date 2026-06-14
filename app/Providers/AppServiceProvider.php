<?php

namespace App\Providers;

use App\Models\Player;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuth();
        $this->configureLocale();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureAuth(): void
    {
        auth()->viaRequest('session-token', function ($request) {
            $token = $request->cookie('session_token');

            return $token ? Player::where('session_token', $token)->first() : null;
        });
    }

    protected function configureLocale(): void
    {
        $locale = session('locale');

        if ($locale && in_array($locale, ['en', 'fr'])) {
            app()->setLocale($locale);
        }
    }
}
