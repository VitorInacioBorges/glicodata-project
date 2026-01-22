<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $nome = "Vitor";
    $array = [10, 20, 30, 40, 50];

    return view('welcome',
    [
        "nome" => $nome,
        "array" => $array,
    ]);
});

Route::get("/user/{id?}", function ($id = null) {

    $search = request("search");

    return view("user", 
    [
        "search" => $search,
        "id" => $id, 
    ]);
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