<?php

use App\Http\Controllers\AuthControllers\WebAuthController;
use App\Http\Controllers\UbsControllers\AdminUbsController;
use App\Http\Controllers\UbsControllers\UbsProfileController;
use App\Http\Controllers\UbsControllers\UbsRegistrationController;
use App\Http\Controllers\Web\AssessmentWebController;
use App\Http\Controllers\Web\PatientWebController;
use App\Http\Controllers\Web\ProfessionalWebController;
use App\Http\Controllers\Web\ReportWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login/ubs')->name('home');

Route::view('/login/ubs', 'ubs.auth.login')
    ->middleware('guest:ubs')
    ->name('ubs.login');

Route::view('/login/admin', 'admin.auth.login')
    ->middleware('guest:admin')
    ->name('admin.login');

Route::post('/login', [WebAuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login');

Route::get('/cadastro/ubs', [UbsRegistrationController::class, 'create'])
    ->name('ubs.register');
Route::post('/cadastro/ubs', [UbsRegistrationController::class, 'store'])
    ->middleware('throttle:ubs-registration')
    ->name('ubs.register.store');

Route::middleware(['auth:ubs', 'auth.session', 'account:ubs'])
    ->prefix('ubs')
    ->name('ubs.')
    ->group(function (): void {
        Route::view('/lobby', 'ubs.lobby')->name('lobby');

        Route::view('/conta/senha', 'auth.password', ['accountType' => 'ubs'])->name('password.edit');
        Route::get('/conta/perfil', [UbsProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/conta/perfil', [UbsProfileController::class, 'update'])->name('profile.update');
        Route::put('/conta/senha', [WebAuthController::class, 'changePassword'])
            ->defaults('accountType', 'ubs')
            ->name('password.update');
        Route::post('/logout', [WebAuthController::class, 'logout'])
            ->defaults('accountType', 'ubs')
            ->name('logout');
    });

Route::middleware(['auth:ubs', 'auth.session', 'account:ubs'])
    ->prefix('ubs')
    ->name('ubs.')
    ->group(function (): void {
        Route::get('/profissionais/busca', [ProfessionalWebController::class, 'search'])
            ->name('professionals.search');
        Route::resource('profissionais', ProfessionalWebController::class)
            ->parameters(['profissionais' => 'id'])
            ->names('professionals')
            ->whereUuid('id');
    });

Route::middleware(['auth:ubs', 'auth.session', 'account:ubs'])
    ->prefix('ubs')
    ->name('ubs.')
    ->group(function (): void {
        Route::resource('pacientes', PatientWebController::class)
            ->parameters(['pacientes' => 'id'])
            ->names('patients')
            ->whereUuid('id');

        Route::put('/avaliacoes/{id}/concluir', [AssessmentWebController::class, 'complete'])
            ->whereUuid('id')
            ->name('assessments.complete');
        Route::resource('avaliacoes', AssessmentWebController::class)
            ->parameters(['avaliacoes' => 'id'])
            ->names('assessments')
            ->whereUuid('id');

        Route::get('/relatorios/exportar', [ReportWebController::class, 'export'])
            ->name('reports.export');
        Route::get('/relatorios/{id}/pdf', [ReportWebController::class, 'pdf'])
            ->whereUuid('id')
            ->name('reports.pdf');
        Route::resource('relatorios', ReportWebController::class)
            ->parameters(['relatorios' => 'id'])
            ->names('reports')
            ->whereUuid('id');

    });

Route::middleware(['auth:admin', 'auth.session', 'account:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::get('/ubs', [AdminUbsController::class, 'index'])->name('ubs.index');
        Route::get('/ubs/{id}/editar', [AdminUbsController::class, 'edit'])
            ->whereUuid('id')
            ->name('ubs.edit');
        Route::put('/ubs/{id}', [AdminUbsController::class, 'update'])
            ->whereUuid('id')
            ->name('ubs.update');
        Route::view('/conta/senha', 'auth.password', ['accountType' => 'admin'])->name('password.edit');
        Route::put('/conta/senha', [WebAuthController::class, 'changePassword'])
            ->defaults('accountType', 'admin')
            ->name('password.update');
        Route::post('/logout', [WebAuthController::class, 'logout'])
            ->defaults('accountType', 'admin')
            ->name('logout');
    });
