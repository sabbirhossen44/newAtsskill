<?php

namespace App\Enums;

enum LandingPageStatusEnum: string
{
    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case INACTIVE = 'inactive';
}
