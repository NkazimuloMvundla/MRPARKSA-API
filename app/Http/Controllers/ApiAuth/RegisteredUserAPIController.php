<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Debugbar;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // Import ValidationException
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse; // Ensure this import is presen
use Illuminate\Support\Facades\Http;

class RegisteredUserAPIController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Debug the request
        //dd($request);

        try {
            // Step 1: Validate the Turnstile token
            $recaptchaToken = $request->input('recaptcha_token');
            $secretKey = '6LcG5i8aAAAAAFw6CNyOae6kIB0xDaeQDvEpMxoq'; // Your secret key

            // Verify the reCAPTCHA response
            $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaToken,
            ]);

            $responseBody = json_decode($response->body());

            if (!$responseBody->success) {
                return response()->json(['error' => 'reCAPTCHA verification failed.'], 400);
            }
            // Step 3: Validate other input data
            $validatedData = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'surname' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'accountType' => ['required', 'string', 'max:255'],
            ]);

            // Step 4: Create the user
            $user = User::create([
                'name' => $validatedData['name'] ?? null,
                'email' => $validatedData['email'],
                'surname' => $validatedData['surname'] ?? null,
                'password' => Hash::make($validatedData['password']),
                'account_type' => $validatedData['accountType'],
            ]);

            // Step 5: Trigger the registered event and log the user in
            event(new Registered($user));
            Auth::login($user);

            // Step 6: Return success response
            return response()->json(['message' => 'User Registered successfully', 'user' => $user], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log and return a generic error message
            Log::error('Error while registering user: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to register user', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUsers(Request $request): JsonResponse
    {

        //  dd($request);
        // Fetch all users
        $allUsers = User::all();

        // Return users in JSON format
        return response()->json(['message' => 'Users retrieved successfully', 'users' => $allUsers], 200);
    }

    public function deleteAllUsers(Request $request): JsonResponse
    {
        dd($request);
        // Delete all users
        User::truncate();

        // Return a response
        return response()->json(['message' => 'All users have been deleted.'], 200);
    }

    public function test(Request $request): JsonResponse
    {

        // Return users in JSON format
        return response()->json(['message' => 'Users retrieved successfully', 'users'], 200);
    }
}
