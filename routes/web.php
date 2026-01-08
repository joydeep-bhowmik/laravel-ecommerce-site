<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


// Route::view('/', 'welcome');

Route::get('dashboard', function (Request $request) {
    $user = $request->user();

    if (!$user->isAdmin()) {
        return redirect('/');
    }

    return view('dashboard', compact('user'));
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::any('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');


Route::get('/products/suggestions', [ProductController::class, 'suggestions']);

Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'store'])->name('cart.add')->middleware('auth');

require __DIR__ . '/auth.php';
