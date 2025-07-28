<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .otp-code {
            background-color: #3498db;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .security-tips {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
            <h2>Password Reset Request</h2>
        </div>

        <p>Hello,</p>
        
        <p>We received a request to reset the password for your account associated with <strong>{{ $email }}</strong>.</p>
        
        <p>Your One-Time Password (OTP) for password reset is:</p>
        
        <div class="otp-code">{{ $otp }}</div>
        
        <div class="warning">
            <strong>⚠️ Important:</strong> This OTP will expire after 10 minutes.
        </div>
        
        <div class="security-tips">
            <h4>🔒 Security Tips:</h4>
            <ul>
                <li>Never share this OTP with anyone</li>
                <li>If you didn't request this reset, please ignore this email</li>
                <li>Use this OTP only on the official {{ $appName }} website - https://it.crystalgalleries.co.uk</li>
            </ul>
        </div>
        
        <p>To reset your password:</p>
        <ol>
            <li>Go to the password reset page on our website</li>
            <li>Enter your email address</li>
            <li>Enter the OTP code above</li>
            <li>Create your new password</li>
        </ol>
        
        <p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
        
        <p>Best regards,<br>
        The {{ $appName }} Team</p>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
