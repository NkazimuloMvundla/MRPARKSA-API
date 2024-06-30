<?php

// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\ApiAuth\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Attempt to reset the user's password
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            // Check the status of the password reset attempt
            if ($status != Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                ]);
            }

            return response()->json(['status' => __($status)]);
        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json(['message' => 'An error occurred during the password reset process. Please try again later.'], 500);
        }
    }

}
