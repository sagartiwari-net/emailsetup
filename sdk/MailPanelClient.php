<?php

/**
 * Simple PHP client for Mail Panel API.
 *
 * Usage:
 * $client = new MailPanelClient('https://mail-api.yourdomain.com', 'mk_your_api_key');
 * $client->sendOtp('user@example.com', '482910', 'Rahul');
 */
class MailPanelClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    public function send(string $to, string $template, array $data = [], ?string $subject = null): array
    {
        $payload = [
            'to' => $to,
            'template' => $template,
            'data' => $data,
        ];

        if ($subject !== null) {
            $payload['subject'] = $subject;
        }

        return $this->request('POST', '/api/v1/send', $payload);
    }

    public function sendOtp(string $to, string $otp, string $name = 'User', int $minutes = 10): array
    {
        return $this->send($to, 'otp', [
            'otp' => $otp,
            'name' => $name,
            'minutes' => $minutes,
        ]);
    }

    public function status(string $messageId): array
    {
        return $this->request('GET', '/api/v1/status/'.$messageId);
    }

    public function todayStats(): array
    {
        return $this->request('GET', '/api/v1/stats/today');
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        $url = rtrim($this->baseUrl, '/').$path;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: '.$this->apiKey,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new RuntimeException('API request failed: '.curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid API response.');
        }

        if ($httpCode >= 400) {
            throw new RuntimeException($decoded['message'] ?? 'API error');
        }

        return $decoded;
    }
}
