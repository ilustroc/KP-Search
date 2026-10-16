<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SearchController::class, 'index'])->name('search.index');
Route::post('/buscar', [SearchController::class, 'store'])
    ->middleware('throttle:search')
    ->name('search.store');
