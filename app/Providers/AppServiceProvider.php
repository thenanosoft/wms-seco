<?php

namespace App\Providers;

use App\Models\AppSetting;
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
        // NOTE: During installation / first migrate, tables might not exist yet.
        // So we guard with Schema::hasTable.
        $storeName = 'Warehouse Store Management System';
        $timezone = 'Asia/Karachi';

        if (Schema::hasTable('app_settings')) {
            $storeName = (string) AppSetting::get('store_name', $storeName);
            $timezone = (string) AppSetting::get('timezone', $timezone);
        }

        // Apply globally
        config(['app.name' => $storeName]);
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        // Share to all views
        view()->share('storeName', $storeName);
        view()->share('appTimezone', $timezone);
    }
}
