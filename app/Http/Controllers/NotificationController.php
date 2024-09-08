<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'app_id' => 'required|string',
                'contents' => 'required|array',
                'include_subscription_ids' => 'required|array',
                // Add any other required fields here
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('ONESIGNAL_API_KEY','NDFmNmViODktNzRmZC00MmYzLWJhYjAtY2RlZmIyOWUyMzQz'), // Use environment variable for the API key
            ])->post('https://onesignal.com/api/v1/notifications', $validatedData);

            // Check if the response is successful
            if ($response->successful()) {
                return response()->json($response->json(), 200);
            } else {
                // Return detailed error information
                return response()->json([
                    'error' => 'Failed to send notification',
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                ], $response->status());
            }
        } catch (ValidationException $e) {
            // Handle the validation exception
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
