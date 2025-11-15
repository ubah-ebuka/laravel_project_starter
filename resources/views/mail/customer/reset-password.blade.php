<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width,initial-scale=1"/>
        <title>Password Reset</title>
    </head>
    <body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#333;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f4f6f8">
            <tr>
                <td align="center" style="padding:30px 15px;">
                    <!-- Card -->
                    <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width:600px; background:#ffffff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                        <tr>
                            <td align="center" bgcolor="#0b76ef" style="padding:30px; border-radius:8px 8px 0 0; color:#fff;">
                            <h1 style="margin:0; font-size:22px; font-weight:700;">Password Reset</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px; text-align:center;">
                            <p style="font-size:15px; line-height:1.5; margin:0 0 25px; color:#444;">
                                We received a request to reset your password. Click the button below to continue. 
                                This link will expire in <strong>10 minutes</strong>.
                            </p>

                            <!-- Centered button -->
                            <table border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto;">
                                <tr>
                                <td align="center" bgcolor="#0b76ef" style="border-radius:6px;">
                                    <a href="{!! $data['resetUrl'] !!}" target="_blank"
                                    style="display:inline-block; padding:14px 28px; font-size:16px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:6px; background-color:#0b76ef;">
                                    Reset Password
                                    </a>
                                </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#777; margin-top:30px;">
                                If you didn’t request a password reset, you can safely ignore this email.
                            </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:20px; font-size:12px; color:#aaa; border-top:1px solid #eee;">
                                © {{ date('Y') }} {{env('APP_NAME')}}. All rights reserved.
                            </td>
                        </tr>
                    </table>
                    <!-- End Card -->
                </td>
            </tr>
        </table>
    </body>
</html>