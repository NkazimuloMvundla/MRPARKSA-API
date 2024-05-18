<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApiAuth\RegisteredUserAPIController;
use App\Http\Controllers\ApiAuth\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/users', [RegisteredUserAPIController::class, 'getUsers'])
->middleware('guest')
                ->name('getUsers');

Route::post('/register', [RegisteredUserAPIController::class, 'store'])
                ->middleware('guest')
                ->name('register');

//THIS IS FOR TESTING PERPUSES COMMENT OUT WHEN DONE
Route::post('/deleteAllUsers', [RegisteredUserAPIController::class, 'deleteAllUsers'])
    ->middleware(['token.present', 'token.valid', 'auth:sanctum'])
    ->name('deleteAllUsers');


Route::middleware('guest')->post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');

// Route::post('/tokens/create', function (Request $request) {
//     $token = $request->user()->createToken($request->token_name);

//     return ['token' => $token->plainTextToken];
// });

// Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
