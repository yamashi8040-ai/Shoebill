<?php

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
