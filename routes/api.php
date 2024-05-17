<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApiAuth\RegisteredUserAPIController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/users', [RegisteredUserAPIController::class, 'getUsers'])
                ->middleware('guest')
                ->name('users');

Route::post('/register', [RegisteredUserAPIController::class, 'store'])
                ->middleware('guest')
                ->name('register');
