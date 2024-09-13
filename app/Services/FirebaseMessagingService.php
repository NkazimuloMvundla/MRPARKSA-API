<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseMessagingService
{
    protected $messaging;

    public function __construct()
    {
        $firebaseCredentialsUrl = env('FIREBASE_CREDENTIALS');

        // Fetch file contents from the remote URL
        $firebaseCredentials = file_get_contents($firebaseCredentialsUrl);

        $factory = (new Factory)->withServiceAccount($firebaseCredentials);

        $this->messaging = $factory->createMessaging();
    }

    public function sendMessageToDevice($deviceToken, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function sendMessageToTopic($topic, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function sendMulticastMessage(array $deviceTokens, $title, $body, $data = [])
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->sendMulticast($message, $deviceTokens);
    }

    // Add more methods as needed
}
