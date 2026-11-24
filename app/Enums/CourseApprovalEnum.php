<?php

namespace App\Enums;

enum CourseApprovalEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
