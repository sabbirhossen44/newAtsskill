<?php

namespace App\Enums;

enum CourseStatusEnum: string
{
    case ACTIVE = 'active';
    case IS_DRAFT = 'is_draft';
    case INACTIVE = 'inactive';
}
