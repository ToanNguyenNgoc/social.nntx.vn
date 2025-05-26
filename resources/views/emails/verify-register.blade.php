<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OTP Verification</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f6f9fc;
      margin: 0;
      padding: 0;
    }

    .email-container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .email-header {
      background-color: #007bff;
      color: #ffffff;
      padding: 20px;
      text-align: center;
    }

    .email-body {
      padding: 30px;
      color: #333333;
    }

    .otp-code {
      font-size: 32px;
      font-weight: bold;
      background-color: #f1f1f1;
      padding: 12px 24px;
      display: inline-block;
      border-radius: 6px;
      margin: 20px 0;
      letter-spacing: 5px;
      color: #007bff;
    }

    .footer {
      padding: 20px;
      font-size: 13px;
      color: #999999;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="email-container">
    <div class="email-header">
      <h2>Email Verification</h2>
    </div>
    <div class="email-body">
      <p>Hello <strong>{{ $email }}</strong>,</p>
      <p>Use the following OTP code to verify your account:</p>

      <div class="otp-code">{{ $otp }}</div>

      <p>This code is valid for <strong>3 minutes</strong>. Please do not share it with anyone.</p>
      <p>If you did not request this, please ignore this email.</p>
    </div>
    <div class="footer">
      &copy; XSocial. All rights reserved.
    </div>
  </div>
</body>

</html>