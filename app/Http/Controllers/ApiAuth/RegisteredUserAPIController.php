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
                'name' => ['string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'surname' => ['string', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'accountType' => ['required', 'string', 'max:255'],
            ]);
           // dd($validatedData);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'surname' => $validatedData['surname'],
                'password' => Hash::make($validatedData['password']),
                'account_type' => $validatedData['accountType']
            ]);

            event(new Registered($user));

            Auth::login($user);
            return response()->json(['message' => 'User Registered successfully', 'users' => $user], 200);

        }catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(), // Use the error message from the exception
            ], 422);
        } catch (\Exception $e) {
           // dd($e);
            Log::error('Error while registering user: ' . $e->getMessage());
            // return response()->json(['message' => 'Failed to register user'], 500);
            return response()->json(['message' => 'Failed to register', 'errors' => $e], 500);
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
    {dd($request);
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

