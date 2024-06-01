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
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\ReviewController;
// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/users', [RegisteredUserAPIController::class, 'getUsers'])
                ->middleware('guest')
                ->name('getUsers');

Route::post('/register', [RegisteredUserAPIController::class, 'store'])
                ->middleware('guest')
                ->name('register');

// Route::post('/search-parking', [RegisteredUserAPIController::class, 'store'])
// ->middleware('guest')
// ->name('search-parking');

//THIS IS FOR TESTING PERPUSES COMMENT OUT WHEN DONE
Route::post('/deleteAllUsers', [RegisteredUserAPIController::class, 'deleteAllUsers'])
    ->middleware(['token.present', 'token.valid', 'auth:sanctum'])
    ->name('deleteAllUsers');

Route::middleware('guest')->post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');


//user-parking-routes

Route::get('/find-parking', [ParkingController::class, 'findParking']);
Route::get('/parking-space/{id}', [ParkingController::class, 'getParkingSpace']);
Route::get('/parking-types', [ParkingController::class, 'listParkingTypes']);

//reservation
Route::get('/user-reservations', [ReservationController::class, 'listUserReservations'])->middleware('auth:sanctum');
Route::delete('/cancel-reservation/{id}', [ReservationController::class, 'cancelReservation'])->middleware('auth:sanctum');
Route::post('/make-reservation', [ParkingController::class, 'makeReservation']); //->middleware('auth:sanctum');

//reviews
Route::get('/parking-space/{id}/reviews', [ReviewController::class, 'listReviews']);
Route::get('/user-reviews', [ReviewController::class, 'listUserReviews'])->middleware('auth:sanctum');
Route::post('/submit-review', [ParkingController::class, 'submitReview']);

//ADMIN-parking-routes
Route::post('/create-parking-space', [ParkingController::class, 'createParkingSpace'])->middleware('auth:sanctum');
Route::put('/parking-space/{id}', [ParkingController::class, 'updateParkingSpace'])->middleware('auth:sanctum');
Route::delete('/parking-space/{id}', [ParkingController::class, 'deleteParkingSpace'])->middleware('auth:sanctum');

// Route::post('/tokens/create', function (Request $request) {
//     $token = $request->user()->createToken($request->token_name);

//     return ['token' => $token->plainTextToken];
// });
