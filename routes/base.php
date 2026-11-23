<?php

use App\Http\Controllers\UpdateStatusController;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Livewire\Installer\PanelInstaller;
use App\Services\Helpers\LanguageService;
use Illuminate\Support\Facades\Route;

Route::get('installer', PanelInstaller::class)->name('installer')
    ->withoutMiddleware(['auth', RedirectIfNotInstalled::class]);

Route::get('update/status', UpdateStatusController::class)->name('update.status')
    ->middleware('auth');

// Licence notices must be reachable without an account.
Route::view('credits', 'credits')->name('credits')
    ->withoutMiddleware(['auth', RedirectIfNotInstalled::class]);

// Language switcher in the top bar and on the sign-in page.
Route::get('language/{code}', function (string $code, LanguageService $languages) {
    abort_unless(array_key_exists($code, $languages->getAvailableLanguages()), 404);
    session()->put('locale', $code);
    user()?->update(['language' => $code]);

    return redirect()->back();
})->name('language.switch')->withoutMiddleware(['auth', RedirectIfNotInstalled::class]);
