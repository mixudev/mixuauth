<?php

namespace App\Shared\Enums;

enum MfaType: string
{
    case TOTP  = 'totp';
    case EMAIL = 'email';
}
