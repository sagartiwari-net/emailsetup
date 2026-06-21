<?php
/**
 * Demo website — OTP login flow using Mail Panel SDK.
 *
 * Setup:
 * 1. Copy config.example.php to config.php
 * 2. Set MAIL_PANEL_URL and MAIL_PANEL_API_KEY from admin panel
 * 3. Run: php -S localhost:8080 in this folder
 */

require_once __DIR__.'/../sdk/MailPanelClient.php';

$config = file_exists(__DIR__.'/config.php')
    ? require __DIR__.'/config.php'
    : require __DIR__.'/config.example.php';

session_start();

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (! $email) {
        $error = 'Valid email required.';
    } else {
        $otp = (string) random_int(100000, 999999);
        $_SESSION['demo_otp'] = $otp;
        $_SESSION['demo_email'] = $email;

        try {
            $client = new MailPanelClient($config['mail_panel_url'], $config['mail_panel_api_key']);
            $result = $client->sendOtp($email, $otp, 'Demo User');

            if (($result['success'] ?? false) || isset($result['message_id'])) {
                $message = 'OTP sent! Check your mail log (local: storage/logs). Message ID: '.($result['message_id'] ?? 'queued');
            } else {
                $error = $result['error'] ?? 'Send failed.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

if (isset($_GET['verify']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered = $_POST['otp'] ?? '';
    if ($entered === ($_SESSION['demo_otp'] ?? '')) {
        $message = 'OTP verified successfully for '.$_SESSION['demo_email'];
        unset($_SESSION['demo_otp']);
    } else {
        $error = 'Invalid OTP.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Website — OTP Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f0a1a; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #1a1229; border: 1px solid #3d2a5c; border-radius: 16px; padding: 32px; width: 100%; max-width: 420px; }
        h1 { font-size: 22px; margin-bottom: 8px; color: #c4b5fd; }
        p { color: #a78bfa; font-size: 14px; margin-bottom: 24px; }
        label { display: block; font-size: 13px; margin-bottom: 6px; color: #ddd; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #3d2a5c; background: #0f0a1a; color: #fff; margin-bottom: 16px; }
        button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff; font-weight: 600; cursor: pointer; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .success { background: #064e3b; color: #6ee7b7; }
        .error { background: #450a0a; color: #fca5a5; }
        a { color: #c4b5fd; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Demo Shop</h1>
        <p>Pilot OTP integration with Mail Panel</p>

        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if (isset($_SESSION['demo_otp']) && ! isset($_GET['verify'])): ?>
            <p>OTP sent to <?= htmlspecialchars($_SESSION['demo_email']) ?>. <a href="?verify=1">Enter OTP</a></p>
        <?php elseif (isset($_GET['verify'])): ?>
            <form method="POST" action="?verify=1">
                <label>Enter OTP</label>
                <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required autofocus>
                <button type="submit">Verify</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@gmail.com" required>
                <button type="submit">Send OTP</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
