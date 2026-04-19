<?php

namespace App\Shared\Enums;

enum DecisionType: string
{
    case ALLOW   = 'ALLOW';
    case OTP     = 'OTP';
    case BLOCK   = 'BLOCK';
    case PENDING = 'PENDING';
}
