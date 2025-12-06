<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => 'Invalid Invite - AllowanceLab'])
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

        .error-container {
            background: white;
            padding: 60px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
        }

        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1f2937;
            margin: 0 0 15px 0;
            font-size: 28px;
        }

        p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Oops!</h1>
        <p>{{ $message }}</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>

</html>