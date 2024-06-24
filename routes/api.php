<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::controller(PostController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/posts', 'index');
        Route::post('/posts', 'store');
        Route::put('/posts/{post}', 'update');
        Route::delete('/posts/{post}', 'destroy');
    });

