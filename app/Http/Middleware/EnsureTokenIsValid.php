<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Sanctum;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        dd($token);
        if (!$token) {
            return response()->json(['message' => 'Authentication token is missing.'], 401);
        }

        try {
            $user = Sanctum::actingAs($request);
            if (!$user) {
                throw new \Exception('Token not valid');
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid or expired authentication token.'], 401);
        }

        return $next($request);
    }
}
