<?php

namespace App\Enums;

enum RefundStatusEnum: string
{
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case SUCCESS = 'success';
}
