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

Route::post('/login', function () {
    return redirect()->route('ubs.auth.login');
})->name('web');
