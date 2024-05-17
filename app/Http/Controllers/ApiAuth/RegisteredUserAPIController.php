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

class RegisteredUserAPIController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request):JsonResponse
    {
        //dd($request);

        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            event(new Registered($user));

            Auth::login($user);
            return response()->json(['message' => 'User Registered successfully', 'users' => $user], 200);

        } catch (ValidationException $e) {
           // dd($e);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
           // dd($e);
            Log::error('Error while registering user: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to register user'], 500);
        }
    }

    public function getUsers(Request $request): JsonResponse
    {
        // Fetch all users
        $allUsers = User::all();

        // Return users in JSON format
        return response()->json(['message' => 'Users retrieved successfully', 'users' => $allUsers], 200);
    }
}
