<?php

namespace App\Http\Controllers;

use App\Services\FirebaseMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Optional, for clarity

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



}
