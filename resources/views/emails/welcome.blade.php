<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #4F46E5;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to AllowanceLab, {{ $user->first_name }}!</h1>
        <p>Thanks for joining AllowanceLab. We're excited to help you teach your kids about financial responsibility!
        </p>
        <p>Get started by adding your first child to your family.</p>
        <p>If you have any questions, please email allowancelab@gmail.com.</p>
        <p>Happy experimenting!<br>The AllowanceLab Team</p>
    </div>
</body>

</html>