<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApiAuth\RegisteredUserAPIController;
use App\Http\Controllers\ApiAuth\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\ApiAuth\Auth\NewPasswordController;
use App\Http\Controllers\ApiAuth\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


// Route::get('/users', [RegisteredUserAPIController::class, 'getUsers'])
//                 ->middleware('guest')
//                 ->name('getUsers');
Route::middleware([EnsureFrontendRequestsAreStateful::class])->group(function () {
    // dd("herte");
    Route::post('/register', [RegisteredUserAPIController::class, 'store'])->middleware('guest')->name('register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('guest')
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name('password.store');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['auth', 'signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});



// Route::post('/search-parking', [RegisteredUserAPIController::class, 'store'])
// ->middleware('guest')
// ->name('search-parking');
Route::get('/test', function () {
   // echo asset('storage/mrparksa-bf841-firebase-adminsdk-erp7l-0a5c129d75.json');
   $firebaseCredentialsUrl = env('FIREBASE_CREDENTIALS');

    // Check if the path is set and file exists
    if (file_exists($firebaseCredentialsUrl)) {
        $fileContents = file_get_contents($firebaseCredentialsUrl);
    } else {
        $fileContents = 'File not found or path is incorrect.';
    }

    return response()->json([
        'message' => 'API is working',
        'firebase_credentials_path' => $firebaseCredentialsUrl,
        'file_contents' => $fileContents,
    ]);
});


//THIS IS FOR TESTING PERPUSES COMMENT OUT WHEN DONE
Route::post('/deleteAllUsers', [RegisteredUserAPIController::class, 'deleteAllUsers'])
    ->middleware(['token.present', 'token.valid', 'auth:sanctum'])
    ->name('deleteAllUsers');



//user-parking-routes
Route::post('/send-oneSignalnotification', [NotificationController::class, 'sendOneSignalNotification'])->middleware('auth:sanctum'); //mobile
Route::post('/send-notification', [NotificationController::class, 'sendNotification'])->middleware('auth:sanctum'); //FCM web
Route::get('/find-hourly-parking', [ParkingController::class, 'findHourlyParking'])->middleware('auth:sanctum');
Route::get('/find-airport-parking', [ParkingController::class, 'findAirportParking'])->middleware('auth:sanctum');
Route::get('/parking-space/{id}', [ParkingController::class, 'getParkingSpace'])->middleware('auth:sanctum');
Route::get('/parking-types', [ParkingController::class, 'listParkingTypes'])->middleware('auth:sanctum');
Route::get('/check-availability', [ParkingController::class, 'checkAvailability']);
Route::post('/make-reservation', [ReservationController::class, 'makeReservation'])->middleware('auth:sanctum');
//reservation
// Route::get('/user-reservations', [ReservationController::class, 'listUserReservations'])->middleware('auth:sanctum');
// Route::delete('/cancel-reservation/{id}', [ReservationController::class, 'cancelReservation'])->middleware('auth:sanctum');
// Route::post('/make-reservation', [ParkingController::class, 'makeReservation']); //->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/reservations', [ReservationController::class, 'listUserReservations']);
    Route::get('/owner/reservations', [ReservationController::class, 'listOwnerReservations']);
});
//reviews
Route::get('/parking-space/{id}/reviews', [ReviewController::class, 'listReviews']);
Route::get('/user-reviews/{parkingSpaceId}', [ReviewController::class, 'listUserReviews'])->middleware('auth:sanctum');
Route::post('/submit-review', [ReviewController::class, 'submitReview'])->middleware('auth:sanctum');

//ADMIN-parking-routes
Route::post('/create-parking-space', [ParkingController::class, 'createParkingSpace'])->middleware('auth:sanctum');
Route::put('/parking-space/{id}', [ParkingController::class, 'updateParkingSpace'])->middleware('auth:sanctum');
Route::delete('/parking-space/{id}', [ParkingController::class, 'deleteParkingSpace'])->middleware('auth:sanctum');
Route::get('/getMyParkingSpaces', [ParkingController::class, 'getMyParkingSpaces'])->middleware('auth:sanctum'); //get my parking spaces
Route::put('/approve-reservation/{id}', [ReservationController::class, 'approveReservation'])->middleware('auth:sanctum');
Route::put('/reject-reservation/{id}', [ReservationController::class, 'rejectReservation'])->middleware('auth:sanctum');
Route::post('/add-admin', [ParkingController::class, 'addAdmin'])->middleware('auth:sanctum');
Route::post('/update-pricing', [ParkingController::class, 'updatePricing'])->middleware('auth:sanctum');

// routes/web.php or routes/api.php
Route::post('/assign-admin', [ParkingController::class, 'assignAdmin']);


// Route::post('/tokens/create', function (Request $request) {
//     $token = $request->user()->createToken($request->token_name);

//     return ['token' => $token->plainTextToken];
// });
