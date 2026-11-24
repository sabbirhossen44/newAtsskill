<?php

namespace App\Enums;

enum RedirectTypeEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case ERROR = 'error';
}
