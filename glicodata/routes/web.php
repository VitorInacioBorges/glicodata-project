<?php

use App\Http\Controllers\UbsControllers\UbsAuthController;
use Illuminate\Support\Facades\Route;

$authDisabled = (bool) config('glicodata.auth_disabled');

Route::redirect('/', '/login')->name('home');

Route::get('/login', function () use ($authDisabled) {
    return $authDisabled || auth('ubs')->check()
        ? redirect()->route('ubs.lobby')
        : view('ubs.auth.login');
})->name('login');

Route::get('/auth/ubs/redirect', [UbsAuthController::class, 'webRedirect'])
    ->name('web.ubs.auth.redirect');

Route::get('/auth/ubs/callback', [UbsAuthController::class, 'webCallback'])
    ->name('web.ubs.auth.callback');

Route::middleware($authDisabled ? [] : ['auth:ubs'])
    ->prefix('ubs')
    ->name('ubs.')
    ->group(function (): void {
        Route::view('/lobby', 'ubs.lobby')->name('lobby');

        Route::view('/pacientes', 'ubs.patients.index')->name('patients.index');
        Route::view('/pacientes/{id}', 'ubs.patients.show')
            ->whereUuid('id')
            ->name('patients.show');

        Route::view('/profissionais', 'ubs.professionals.index')->name('professionals.index');
        Route::view('/profissionais/{id}', 'ubs.professionals.show')
            ->whereUuid('id')
            ->name('professionals.show');

        Route::view('/avaliacoes', 'ubs.assessments.index')->name('assessments.index');
        Route::view('/avaliacoes/{id}', 'ubs.assessments.show')
            ->whereUuid('id')
            ->name('assessments.show');

        Route::post('/logout', [UbsAuthController::class, 'webLogout'])->name('logout');
    });
