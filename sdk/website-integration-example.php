<?php

/**
 * Website OTP integration example.
 * Copy MailPanelClient.php to your website and use like this.
 */

require __DIR__.'/MailPanelClient.php';

$config = [
    'api_url' => getenv('MAIL_PANEL_URL') ?: 'http://localhost:8000',
    'api_key' => getenv('MAIL_PANEL_API_KEY') ?: 'your_api_key_here',
];

$client = new MailPanelClient($config['api_url'], $config['api_key']);

// --- Example: Send OTP after login/register ---
function sendLoginOtp(MailPanelClient $client, string $userEmail, string $userName): array
{
    $otp = (string) random_int(100000, 999999);

    // Save $otp in session/DB for verification — your website logic
    // session(['otp' => $otp, 'otp_expires' => now()->addMinutes(10)]);

    return $client->sendOtp($userEmail, $otp, $userName, 10);
}

// --- Example: Check remaining daily cap before bulk ---
function canSendMore(MailPanelClient $client): bool
{
    $stats = $client->todayStats();

    return ($stats['remaining'] ?? 0) > 0;
}

// Demo (comment out in production):
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0] ?? '')) {
    try {
        $result = sendLoginOtp($client, 'test@example.com', 'Demo User');
        echo "OTP sent. Message ID: {$result['message_id']}\n";
    } catch (Throwable $e) {
        echo 'Error: '.$e->getMessage()."\n";
    }
}
