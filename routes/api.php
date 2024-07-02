<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LivroController;
use Illuminate\Support\Facades\Route;


Route::post('v1/auth/token', [AuthController::class, 'getToken']);

Route::middleware('auth:api')->group(function () {
    Route::get('v1/livros', [LivroController::class, 'index']);
    Route::post('v1/livros', [LivroController::class, 'store']);
    Route::post('v1/livros/{livroId}/importar-indices-xml', [LivroController::class, 'importarIndicesXml']);
});
