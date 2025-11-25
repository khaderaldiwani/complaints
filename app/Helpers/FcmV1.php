<?php

namespace App\Helpers;

use Google\Client;

class FcmV1
{
    public static function sendToTopic($topic, $title, $body)
    {
        $projectId = env('FIREBASE_PROJECT_ID');

        // Load credentials
        $client = new Client();
        $client->setAuthConfig(base_path(env('FIREBASE_CREDENTIALS')));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
        // Request URL
        $url = "https://fcm.googleapis.com/v1/projects/complaint-naya/messages:send";

        // Firebase V1 message format
        $message = [
            "message" => [
                "topic" => "$topic",
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "data" => [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                ]
            ]
        ];

        $payload = json_encode($message);

        // Send via CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
