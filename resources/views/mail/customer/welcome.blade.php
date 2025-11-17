<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Welcome</title>
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
            <h2>Welcome</h2>
            <p>
                Hi {{ucwords($data['user']->first_name)}}, <br><br>
                Thank you for registering with us! Welcome to {{config('app.name')}}.
            </p>
            <div class="footer">
                If you didn’t create an account, you can safely ignore this email.
            </div>
        </div>
    </body>
</html>