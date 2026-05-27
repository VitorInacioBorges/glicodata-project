<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/register/{id?}', function (?string $id = null) {
    return view('register', [
        'search' => request('search'),
        'id' => $id,
    ]);
})->name('register');

Route::view('/login', 'ubs.auth.login')->name('ubs.login');

Route::post('/login', function () {
    return redirect()->route('ubs.auth.login');
})->name('web');

Route::prefix('ubs')->name('ubs.')->group(function (): void {
    Route::view('/lobby', 'ubs.lobby')->name('lobby');
    Route::view('/pacientes', 'ubs.patients.index')->name('patients.index');
    Route::view('/medicos', 'ubs.doctors.index')->name('doctors.index');
    Route::view('/avaliacoes', 'ubs.assessments.index')->name('assessments.index');
});
