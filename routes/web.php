<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;

// Public Website Routes
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/search', 'search')->name('search');
    
    // Document routes with nested structure: /{type}/{document}
    Route::get('/{typeSlug}/{documentSlug}', 'document')->name('document.show');
    Route::get('/{typeSlug}/{documentSlug}/download', 'download')->name('document.download');
});

// Admin panel is handled by Filament at /admin
