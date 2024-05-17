<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApiAuth\RegisteredUserAPIController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'This is a test route', 'user' => $request->user()]);
});

Route::post('/testreg', [RegisteredUserAPIController::class, 'store'])
                ->middleware('guest')
                ->name('testreg');
