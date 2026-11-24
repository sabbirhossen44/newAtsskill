<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case INSTRUCTOR = 'instructor';
    case STUDENT = 'student';
}
