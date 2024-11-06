<?php

namespace App\Services;

use GuzzleHttp\Client;
use Google\Client as GoogleClient;
use Log;

class FirebaseNotificationService
{
    protected $httpClient;
    protected $projectId;
    protected $googleClient;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->googleClient = new GoogleClient();

        // إعداد بيانات المصادقة باستخدام JSON
        $this->googleClient->setAuthConfig(config('services.firebase.service_account'));
        $this->googleClient->addScope('https://www.googleapis.com/auth/firebase.messaging');

        // إنشاء HTTP Client
        $this->httpClient = new Client([
            'base_uri' => 'https://fcm.googleapis.com/',
        ]);
    }

    protected function getAccessToken()
    {
        // الحصول على Token
        $token = $this->googleClient->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public function sendNotification(array $deviceTokens, string $title, string $body, array $data = [])
    {
        $url = "v1/projects/{$this->projectId}/messages:send";

        $notification = [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
            'tokens' => $deviceTokens,
        ];

        $message = [
            'message' => [
                'token' => $deviceTokens[0], // أو يمكنك استخدام 'topic' أو 'condition'
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ],
        ];

        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $message,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
