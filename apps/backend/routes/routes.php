<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\UbsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root route
Route::get('/', function () {
    return view('home');
});

// Register route
Route::get("/register/{id?}", function ($id = null) {

    $search = request("search");

    return view(
        "register",
        [
            "search" => $search,
            "id" => $id,
        ]
    );
});

Route::post('/login', function (Request $request) {

    // Aqui você processa os dados vindos do formulário

    $data = $request->all(); // só para teste

    return dd($data); // mostra os dados para confirmar
})->name('web');

Route::prefix('api/v1')->group(function (): void {
    Route::delete('districts/{id}/delete-self', [DistrictController::class, 'deleteSelf']);
    Route::delete('ubs/{id}/delete-self', [UbsController::class, 'deleteSelf']);
    Route::delete('users/{id}/delete-self', [UserController::class, 'deleteSelf']);
    Route::delete('patients/{id}/delete-self', [PatientController::class, 'deleteSelf']);
    Route::delete('assessments/{id}/delete-self', [AssessmentController::class, 'deleteSelf']);
    Route::delete('risks/{id}/delete-self', [RiskController::class, 'deleteSelf']);
    Route::delete('reports/{id}/delete-self', [ReportController::class, 'deleteSelf']);

    Route::apiResource('districts', DistrictController::class);
    Route::apiResource('ubs', UbsController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('assessments', AssessmentController::class);
    Route::apiResource('risks', RiskController::class);
    Route::apiResource('reports', ReportController::class);
});
