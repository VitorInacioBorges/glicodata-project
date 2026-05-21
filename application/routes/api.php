<?php

use App\Http\Controllers\AssessmentControllers\AssessmentController;
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
| GET /api/districts lists paginated districts.
| POST /api/districts creates a district.
| GET /api/districts/{id} shows one district by UUID.
| PUT/PATCH /api/districts/{id} updates one district by UUID.
| DELETE /api/districts/{id} removes one district by UUID.
| DELETE /api/districts/{id}/delete-self removes one district through
| the explicit delete-self action used by the current controllers.
*/
Route::delete('districts/{id}/delete-self', [DistrictController::class, 'deleteSelf']);
Route::apiResource('districts', DistrictController::class)
    ->parameters(['districts' => 'id']);

/*
|--------------------------------------------------------------------------
| UBS API routes
|--------------------------------------------------------------------------
| GET /api/ubs lists paginated UBS units.
| POST /api/ubs creates a UBS unit.
| GET /api/ubs/{id} shows one UBS unit by UUID.
| PUT/PATCH /api/ubs/{id} updates one UBS unit by UUID.
| DELETE /api/ubs/{id} removes one UBS unit by UUID.
| DELETE /api/ubs/{id}/delete-self removes one UBS unit through the
| explicit delete-self action used by the current controllers.
*/
Route::delete('ubs/{id}/delete-self', [UbsController::class, 'deleteSelf']);
Route::apiResource('ubs', UbsController::class)
    ->parameters(['ubs' => 'id']);

/*
|--------------------------------------------------------------------------
| User API routes
|--------------------------------------------------------------------------
| GET /api/users lists paginated system users.
| POST /api/users creates a system user.
| GET /api/users/{id} shows one system user by UUID.
| PUT/PATCH /api/users/{id} updates one system user by UUID.
| DELETE /api/users/{id} removes one system user by UUID.
| DELETE /api/users/{id}/delete-self removes one system user through
| the explicit delete-self action used by the current controllers.
*/
Route::delete('users/{id}/delete-self', [UserController::class, 'deleteSelf']);
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
| DELETE /api/patients/{id} removes one patient by UUID.
| DELETE /api/patients/{id}/delete-self removes one patient through
| the explicit delete-self action used by the current controllers.
*/
Route::delete('patients/{id}/delete-self', [PatientController::class, 'deleteSelf']);
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
| DELETE /api/assessments/{id} removes one assessment by UUID.
| DELETE /api/assessments/{id}/delete-self removes one assessment
| through the explicit delete-self action used by the current controllers.
*/
Route::delete('assessments/{id}/delete-self', [AssessmentController::class, 'deleteSelf']);
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
| DELETE /api/risks/{id} removes one risk record by UUID.
| DELETE /api/risks/{id}/delete-self removes one risk record through
| the explicit delete-self action used by the current controllers.
*/
Route::delete('risks/{id}/delete-self', [RiskController::class, 'deleteSelf']);
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
| DELETE /api/reports/{id} removes one report by UUID.
| DELETE /api/reports/{id}/delete-self removes one report through
| the explicit delete-self action used by the current controllers.
*/
Route::delete('reports/{id}/delete-self', [ReportController::class, 'deleteSelf']);
Route::apiResource('reports', ReportController::class)
    ->parameters(['reports' => 'id']);
});
