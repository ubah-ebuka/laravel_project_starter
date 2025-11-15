<?php

namespace App\Enums;

enum RequestActionEnum: int
{
    case EMAIL_NOT_VERIFIED = 4001;
    case PHONE_NOT_VERIFIED = 4002;
    case TWOFA_NOT_SETUP = 4003;
    case VALIDATION_ERROR = 4222;
    case NOT_AUTHENTICATED = 4010;
    case NOT_AUTHORIZED = 4030;
    case RESOURCE_NOT_FOUND = 4040;
    case REQUEST_ERROR = 4000;
    case SERVER_ERROR = 5000;
    case SUCCESS = 2000;
    case CSRF_TOKEN_MISMATCH = 4190;
}