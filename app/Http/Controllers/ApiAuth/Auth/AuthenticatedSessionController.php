<?php

namespace App\Http\Controllers\ApiAuth\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; // Ensure this import is presen
use App\Models\User;
use Illuminate\Validation\ValidationException; // Import ValidationException

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
       /// dd($request);

        try {
            $request->authenticate();

            $user = User::where('email', $request->email)->first();
           // dd($user);
            //before an admin can change anything, check if they have enough rights, check if thier  userId exist on that specifi space
            $token = $request->user()->createToken('api-token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'username' => $user->name,
                'account_type' => $user->account_type
            ], 201);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json(['message' => 'These credentials do not match our records', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
