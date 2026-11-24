<?php

namespace App\Enums;

enum NewsLetterStatusEnum: string
{
    case NOT_VERIFIED = 'not_verified';
    case VERIFIED = 'verified';
}
