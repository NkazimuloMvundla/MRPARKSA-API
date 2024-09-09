<?php

namespace App\Http\Controllers;

use App\Services\FirebaseMessagingService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseMessaging;

    public function __construct(FirebaseMessagingService $firebaseMessaging)
    {
        $this->firebaseMessaging = $firebaseMessaging;
    }

    public function sendNotification(Request $request)
    {
        dd("testing...");
        $deviceTokens = $request->input('device_tokens'); // Expect an array of tokens
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);

        try {
            foreach ($deviceTokens as $deviceToken) {
                $this->firebaseMessaging->sendMessageToDevice($deviceToken, $title, $body, $data);
            }
            dd($request);
            return response()->json(['message' => 'Notifications sent successfully']);
        } catch (\Exception $e) {
            // Log the exception details
            //\Log::error('Failed to send notifications: ' . $e->getMessage());

            // Return a response with an error message
            return response()->json(['message' => 'Failed to send notifications', 'error' => $e->getMessage()], 500);
        }
    }


}
