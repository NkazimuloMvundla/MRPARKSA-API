<?php

namespace App\Http\Controllers;

use App\Services\FirebaseMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Optional, for clarity
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    protected $firebaseMessaging;

    public function __construct(FirebaseMessagingService $firebaseMessaging)
    {
        $this->firebaseMessaging = $firebaseMessaging;
    }

    public function sendNotification(Request $request)
    {
        $deviceTokens = $request->input('device_tokens'); // Expect an array of tokens
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);

        try {
            foreach ($deviceTokens as $deviceToken) {
                $this->firebaseMessaging->sendMessageToDevice($deviceToken, $title, $body, $data);
            }

            return response()->json(['message' => 'Notifications sent successfully']);
        } catch (\Exception $e) {
            // Log the detailed error message and stack trace
            Log::error('Failed to send notifications: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Return a response with an error message and status code 500
            return response()->json([
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function sendOneSignalNotification(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'app_id' => 'required|string',
                'contents' => 'required|array',
                'include_subscription_ids' => 'required|array',
                'buttons' => 'nullable|array',  // Allow buttons to be passed optionally
            ]);

            // Make the POST request to OneSignal API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . env('ONESIGNAL_API_KEY', 'NDFmNmViODktNzRmZC00MmYzLWJhYjAtY2RlZmIyOWUyMzQz'), // API Key from env
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $validatedData['app_id'],
                'include_player_ids' => $validatedData['include_subscription_ids'],
                'headings' => ['en' => 'Geofence Alert!'],
                'contents' => $validatedData['contents'],
                'small_icon' => 'ic_app_icon', // Path to your app icon
                'large_icon' => 'https://judges.nyc3.cdn.digitaloceanspaces.com/parking.png', // URL to your app icon
                'big_picture' => 'https://judges.nyc3.cdn.digitaloceanspaces.com/parking.png', // URL to large image
                'buttons' => $validatedData['buttons'] ?? [
                    [
                        'id' => 'pay_now',
                        'text' => 'Pay Now',
                        'icon' => 'ic_pay', // Optional: small icon for the button
                    ]
                ],
            ]);

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
