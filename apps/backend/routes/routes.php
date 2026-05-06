<?php

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

//TODO Route::post("/create", function)
//TODO Route::get("/list/patients", function)
//TODO Route::get("/list/users", function)
//TODO Route::get("/user", function)
//TODO Route::delete("/delete", function)
//TODO Route::put("/update", function)