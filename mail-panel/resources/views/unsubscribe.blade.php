<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/cosmic-theme.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-card" style="text-align:center;">
        @if ($success)
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(74,222,128,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fas fa-check" style="font-size:32px;color:#059669;"></i>
            </div>
            <h2>Unsubscribed</h2>
            <p class="login-subtitle">{{ $message }}</p>
            @isset($email)
                <p style="font-size:14px;color:var(--text-secondary);">{{ $email }}</p>
            @endisset
        @else
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fas fa-times" style="font-size:32px;color:#dc2626;"></i>
            </div>
            <h2>Invalid Link</h2>
            <p class="login-subtitle">{{ $message }}</p>
        @endif
    </div>
</div>
</body>
</html>
