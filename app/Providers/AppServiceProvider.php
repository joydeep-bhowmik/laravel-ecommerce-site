<?php
namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

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

        $user = User::where('role', 'admin')->first();

        if (!$user) {

            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);

            Auth::login($user);
        }

        Folio::path(resource_path('views/pages/admin'))
            ->uri('/admin')
            ->middleware([
                '*' => [
                    'auth',
                    'verified',
                    'admin',
                ],
            ]);

        Folio::path(resource_path('views/pages/auth'))
            ->uri('/auth')
            ->middleware([
                '*' => [
                    'guest',
                ],
            ]);

        Folio::path(resource_path('views/pages/account'))
            ->uri('/account')
            ->middleware([
                '*' => [
                    'auth',
                ],
            ]);
    }
}
