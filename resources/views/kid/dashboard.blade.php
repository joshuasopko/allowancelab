<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - AllowanceLab</title>
    @vite(['resources/css/dashboard.css'])
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .placeholder {
            background: white;
            padding: 60px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1f2937;
            margin: 0 0 15px 0;
            font-size: 32px;
        }

        p {
            color: #6b7280;
            font-size: 18px;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-box strong {
            color: #1e40af;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 30px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="placeholder">
        <div class="success-icon">🎉</div>
        <h1>Welcome to AllowanceLab!</h1>
        <p>Your account has been created successfully!</p>

        <div class="info-box">
            <strong>What's Next?</strong><br>
            We're still building your awesome kid dashboard. For now, you're all set up and ready to go!
        </div>

        <form action="{{ route('kid.logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</body>

</html>