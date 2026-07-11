<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        $settings = Schema::hasTable('settings') ? Setting::allCached() : [];

        config([
            'mail.default' => $settings['mail_mailer'] ?? config('mail.default'),
            'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.scheme' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.scheme'),
            'mail.from.address' => $settings['mail_from_address'] ?? $settings['school_email'] ?? config('mail.from.address'),
            'mail.from.name' => $settings['mail_from_name'] ?? $settings['school_name'] ?? config('mail.from.name'),
        ]);

        view()->share('schoolSettings', $settings);
    }
}
