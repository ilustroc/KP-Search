<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchModuleController;
use App\Http\Controllers\SearchResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('search.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('can:search.use')->group(function (): void {
        Route::get('/search', [SearchController::class, 'index'])->name('search.index');
        Route::post('/search', [SearchController::class, 'store'])
            ->middleware('throttle:search')
            ->name('search.store');
        Route::get('/search/result/{token}', [SearchResultController::class, 'show'])
            ->name('search.results.show');
        Route::get('/search/result/{token}/modules/{module}', [SearchModuleController::class, 'show'])
            ->whereIn('module', array_keys(config('search.modules')))
            ->name('search.results.modules.show');
    });
});
