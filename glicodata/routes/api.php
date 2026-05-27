<?php

use App\Http\Controllers\AssessmentControllers\AssessmentController;
use App\Http\Controllers\AuditEventControllers\AuditEventController;
use App\Http\Controllers\DistrictControllers\DistrictController;
use App\Http\Controllers\PatientControllers\PatientController;
use App\Http\Controllers\ReportControllers\ReportController;
use App\Http\Controllers\RiskControllers\RiskController;
use App\Http\Controllers\UbsControllers\UbsAuthController;
use App\Http\Controllers\UbsControllers\UbsController;
use App\Http\Controllers\UserControllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth/ubs')->group(function (): void {
    Route::get('login', [UbsAuthController::class, 'redirect'])->name('ubs.auth.login');
    Route::get('callback', [UbsAuthController::class, 'callback'])->name('ubs.auth.callback');
});

Route::middleware('auth:keycloak')->group(function (): void {
    Route::get('auth/ubs/me', [UbsAuthController::class, 'me'])->name('ubs.auth.me');
    Route::get('auth/ubs/logout', [UbsAuthController::class, 'logout'])->name('ubs.auth.logout');

    /*
    |--------------------------------------------------------------------------
    | District API routes
    |--------------------------------------------------------------------------
    | Institutional district catalog. Authenticated UBS units may list and read;
    | changes are versioned through controlled institutional database updates.
    */
    Route::apiResource('districts', DistrictController::class)
        ->only(['index', 'show'])
        ->parameters(['districts' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | UBS API routes
    |--------------------------------------------------------------------------
    | Each UBS may read its own registration. A Keycloak client role audit-admin
    | may list and update units, including activation after provisional data has
    | been confirmed. Deletion is not exposed by the API.
    */
    Route::apiResource('ubs', UbsController::class)
        ->only(['index', 'show', 'update'])
        ->parameters(['ubs' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | User API routes
    |--------------------------------------------------------------------------
    | GET /api/users lists paginated system users.
    | POST /api/users creates a system user.
    | GET /api/users/{id} shows one system user by UUID.
    | PUT/PATCH /api/users/{id} updates one system user by UUID.
    | DELETE /api/users/{id} performs logical deletion with an audit event.
    */
    Route::apiResource('users', UserController::class)
        ->parameters(['users' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Patient API routes
    |--------------------------------------------------------------------------
    | GET /api/patients lists paginated patients.
    | POST /api/patients creates a patient.
    | GET /api/patients/{id} shows one patient by UUID.
    | PUT/PATCH /api/patients/{id} updates one patient by UUID.
    | DELETE /api/patients/{id} performs logical deletion with an audit event.
    */
    Route::apiResource('patients', PatientController::class)
        ->parameters(['patients' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Assessment API routes
    |--------------------------------------------------------------------------
    | GET /api/assessments lists paginated assessments.
    | POST /api/assessments creates an assessment.
    | GET /api/assessments/{id} shows one assessment by UUID.
    | PUT/PATCH /api/assessments/{id} updates one assessment by UUID.
    | DELETE /api/assessments/{id} logically deletes the assessment, its risk
    | and report, recording audit events in one transaction.
    */
    Route::apiResource('assessments', AssessmentController::class)
        ->parameters(['assessments' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Risk API routes
    |--------------------------------------------------------------------------
    | GET /api/risks lists paginated risks.
    | POST /api/risks creates a risk record.
    | GET /api/risks/{id} shows one risk record by UUID.
    | PUT/PATCH /api/risks/{id} updates one risk record by UUID.
    | DELETE /api/risks/{id} performs logical deletion with an audit event.
    */
    Route::apiResource('risks', RiskController::class)
        ->parameters(['risks' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Report API routes
    |--------------------------------------------------------------------------
    | GET /api/reports lists paginated reports.
    | POST /api/reports creates a report.
    | GET /api/reports/{id} shows one report by UUID.
    | PUT/PATCH /api/reports/{id} updates one report by UUID.
    | DELETE /api/reports/{id} performs logical deletion with an audit event.
    */
    Route::apiResource('reports', ReportController::class)
        ->parameters(['reports' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Audit Event API routes
    |--------------------------------------------------------------------------
    | UBS units may read only their own immutable events. Keycloak client role
    | audit-admin may read all events and redact sensitive snapshots, recording
    | a new permanent audit event for the redaction action.
    */
    Route::get('audit-events', [AuditEventController::class, 'index']);
    Route::get('audit-events/{id}', [AuditEventController::class, 'show']);
    Route::post('audit-events/{id}/redact', [AuditEventController::class, 'redact']);
});
