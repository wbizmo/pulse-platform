<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PluginSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ThemeSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pages', [PageController::class, 'index'])->name('pages');
        Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

        Route::get('/plugins', [PluginController::class, 'index'])->name('plugins');
        Route::post('/plugins/{plugin}/toggle', [PluginController::class, 'toggle'])->name('plugins.toggle');
        Route::get('/plugins/{plugin}/settings', [PluginSettingsController::class, 'index'])->name('plugins.settings');
        Route::post('/plugins/{plugin}/settings', [PluginSettingsController::class, 'update'])->name('plugins.settings.update');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/themes', [ThemeController::class, 'index'])->name('themes');
        Route::post('/themes/{theme}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
        Route::get('/themes/{theme}/settings', [ThemeSettingsController::class, 'index'])->name('themes.settings');
        Route::post('/themes/{theme}/settings', [ThemeSettingsController::class, 'update'])->name('themes.settings.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
