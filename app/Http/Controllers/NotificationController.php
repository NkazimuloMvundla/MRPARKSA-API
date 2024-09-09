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
        $deviceToken = $request->input('device_token');
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);

        $this->firebaseMessaging->sendMessageToDevice($deviceToken, $title, $body, $data);

        return response()->json(['message' => 'Notification sent successfully']);
    }
}
