<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
        //
		 Validator::extend('egypt_mobile', function ($attribute, $value, $parameters, $validator) {
            // Define the regular expression pattern for a valid Egyptian mobile number
            $pattern = '/^(01)[0-2]\d{8}$/';

            // Check if the value matches the pattern
            return (bool) preg_match($pattern, $value);
        });
    }
}
