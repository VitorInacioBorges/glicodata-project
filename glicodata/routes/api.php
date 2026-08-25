<?php

use App\Http\Controllers\AssessmentControllers\AssessmentController;
use App\Http\Controllers\AuditEventControllers\AuditEventController;
use App\Http\Controllers\AuthControllers\ApiAuthController;
use App\Http\Controllers\DistrictControllers\DistrictController;
use App\Http\Controllers\PatientControllers\PatientController;
use App\Http\Controllers\QuestionnaireControllers\QuestionnaireController;
use App\Http\Controllers\ReportControllers\ReportController;
use App\Http\Controllers\RiskControllers\RiskController;
use App\Http\Controllers\UbsControllers\UbsController;
use App\Http\Controllers\UserControllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('auth.login');

Route::middleware('auth:sanctum')->group(function (): void {
    // Logout remains available to an authenticated token even if its account
    // was deactivated, so the credential can always be revoked explicitly.
    Route::post('auth/logout', [ApiAuthController::class, 'logout'])->name('auth.logout');

    Route::middleware('account:ubs,user,admin')->group(function (): void {
        Route::get('auth/me', [ApiAuthController::class, 'me'])->name('auth.me');
        Route::put('auth/password', [ApiAuthController::class, 'changePassword'])->name('auth.password');
    });

    Route::middleware('account:ubs,admin')->group(function (): void {
        Route::apiResource('districts', DistrictController::class)
            ->only(['index', 'show'])
            ->parameters(['districts' => 'id']);

        // UBS see only themselves; administrators may list, read and update units.
        Route::apiResource('ubs', UbsController::class)
            ->only(['index', 'show', 'update'])
            ->parameters(['ubs' => 'id']);

        // UBS see their own events; only administrators may redact snapshots.
        Route::get('audit-events', [AuditEventController::class, 'index']);
        Route::get('audit-events/{id}', [AuditEventController::class, 'show']);
        Route::post('audit-events/{id}/redact', [AuditEventController::class, 'redact']);
    });

    Route::middleware('account:admin')->group(function (): void {
        Route::post('ubs', [UbsController::class, 'store'])->name('ubs.store');
    });

    // A conta institucional ou um gestor individual podem administrar a equipe.
    Route::middleware('account:ubs,user')->group(function (): void {
        Route::apiResource('users', UserController::class)
            ->parameters(['users' => 'id']);
    });

    // Dados clínicos exigem um ator individual; o tenant vem de user.ubs_id.
    Route::middleware('account:user')->group(function (): void {
        Route::apiResource('patients', PatientController::class)
            ->parameters(['patients' => 'id']);

        Route::get('questionnaires/current', [QuestionnaireController::class, 'current'])
            ->name('questionnaires.current');
        Route::get('questionnaire-versions/{id}', [QuestionnaireController::class, 'show'])
            ->whereUuid('id')
            ->name('questionnaire-versions.show');

        Route::post('assessments/{id}/complete', [AssessmentController::class, 'complete'])
            ->whereUuid('id')
            ->name('assessments.complete');
        Route::apiResource('assessments', AssessmentController::class)
            ->parameters(['assessments' => 'id']);

        Route::apiResource('risks', RiskController::class)
            ->only(['index', 'show'])
            ->parameters(['risks' => 'id']);

        Route::get('reports/export', [ReportController::class, 'export'])
            ->name('reports.export');
        Route::apiResource('reports', ReportController::class)
            ->parameters(['reports' => 'id']);
    });
});
