<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Email Verification</title>
        <style>
            body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            }
            .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            }
            h2 {
            color: #333333;
            }
            p {
            color: #555555;
            font-size: 15px;
            line-height: 1.6;
            }
            .button {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 28px;
            background-color: #4CAF50;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            }
            .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999999;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Verify Your Email</h2>
            <p>
                Hi, <br><br>
                Please confirm your email address by clicking the button below.  
                This helps us make sure it’s really you.
            </p>
            <a href="{!! $data['verificationUrl'] !!}" class="button">Verify Email</a>
            <p style="margin-top: 25px; font-size: 13px; color: #777;">
                If the button doesn’t work, copy and paste this link into your browser:<br>
                <span style="color: #4CAF50;">{!! $data['verificationUrl'] !!}</span>
            </p>
            <div class="footer">
                If you didn’t create an account, you can safely ignore this email.
            </div>
        </div>
    </body>
</html>