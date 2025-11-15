<?php

namespace App\Enums;

enum OtpActionTypeEnum: string
{
    case RESET_PASSWORD = 'reset-password';
    case EMAIL_VERIFICATION = 'email-verification';
    case PHONE_VERIFICATION = 'phone-verification';
}