<?php

namespace App\Helpers;

use Google\Client;

class FcmV1
{
    public static function sendToTopic($topic, $title, $body)
    {
        $projectId = env('FIREBASE_PROJECT_ID');

        // Determine credentials file path.
        $envPath = env('FIREBASE_CREDENTIALS');
        if (empty($envPath)) {
            $credentialsPath = storage_path('app/firebase/firebase_credentials.json');
        } else {
            // If env path is absolute use it, otherwise treat as relative to project base
            $isWindowsAbs = preg_match('/^[A-Za-z]:\\\\|^\\\\/', $envPath);
            if (strpos($envPath, '/') === 0 || $isWindowsAbs) {
                $credentialsPath = $envPath;
            } else {
                $credentialsPath = base_path($envPath);
            }
        }

        // If file missing, try to create from FIREBASE_CREDENTIALS_JSON env var
        if (!file_exists($credentialsPath)) {
            $json = env('FIREBASE_CREDENTIALS_JSON');
            if (!empty($json)) {
                $dir = dirname($credentialsPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                @file_put_contents($credentialsPath, $json);
            } else {
                throw new \InvalidArgumentException('Firebase credentials file not found. Set FIREBASE_CREDENTIALS or FIREBASE_CREDENTIALS_JSON in your environment.');
            }
        }

        // Load credentials
        $client = new Client();
        $client->setAuthConfig($credentialsPath);
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
