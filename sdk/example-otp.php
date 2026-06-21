<?php

require __DIR__.'/MailPanelClient.php';

// Example — update these values after deployment
$client = new MailPanelClient(
    baseUrl: 'http://localhost:8000',
    apiKey: 'your_api_key_here',
);

try {
    $result = $client->sendOtp(
        to: 'test@example.com',
        otp: '123456',
        name: 'Sagar',
    );

    echo "Mail queued/sent\n";
    print_r($result);
} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
