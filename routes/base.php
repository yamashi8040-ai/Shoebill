<?php

use App\Http\Controllers\ClassicLocaleController;
use App\Http\Controllers\ClassicUiController;
use App\Http\Controllers\UpdateStatusController;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Livewire\Installer\PanelInstaller;
use Illuminate\Support\Facades\Route;

Route::get('installer', PanelInstaller::class)->name('installer')
    ->withoutMiddleware(['auth', RedirectIfNotInstalled::class]);

Route::get('update/status', UpdateStatusController::class)->name('update.status')
    ->middleware('auth');

// Licence notices must be reachable without an account.
Route::view('credits', 'credits')->name('credits')
    ->withoutMiddleware(['auth', RedirectIfNotInstalled::class]);

// The Pterodactyl client UI, kept as a switchable alternative to the main panel.
Route::get('classic/{path?}', ClassicUiController::class)->where('path', '.*')->name('classic')->middleware('auth');
Route::get('locales/locale.json', ClassicLocaleController::class)->name('classic.locale')->middleware('auth');
