<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\VisualisationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/movements/{slug}', [VisualisationController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('visualisations.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/credits', [PageController::class, 'credits'])->name('credits');

Route::get('/arrangements/{arrangement}', [HomeController::class, 'arrangement'])
    ->name('arrangements.show');

Route::prefix('api')->name('api.')->group(function () {
    Route::post('/arrangements', [ArrangementController::class, 'store'])
        ->middleware('throttle:arrangements')
        ->name('arrangements.store');

    Route::get('/arrangements/{arrangement}', [ArrangementController::class, 'show'])
        ->name('arrangements.show');
});

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->name('admin.')->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/movements', [Admin\VisualisationController::class, 'index'])->name('visualisations.index');
        Route::get('/movements/{visualisation}/edit', [Admin\VisualisationController::class, 'edit'])->name('visualisations.edit');
        Route::put('/movements/{visualisation}', [Admin\VisualisationController::class, 'update'])->name('visualisations.update');

        Route::get('/pages', [Admin\PageController::class, 'index'])->name('pages.index');
        Route::get('/pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');

        Route::get('/arrangements', [Admin\ArrangementController::class, 'index'])->name('arrangements.index');
        Route::delete('/arrangements/{arrangement:id}', [Admin\ArrangementController::class, 'destroy'])->name('arrangements.destroy');

        Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('can:manage-users')->group(function () {
            Route::resource('users', Admin\UserController::class)->except('show');
        });
    });
});
