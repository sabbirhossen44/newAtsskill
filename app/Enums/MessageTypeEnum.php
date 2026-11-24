<?php

namespace App\Enums;

enum MessageTypeEnum: string
{
    case ERROR = 'error';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case INFO = 'info';
}
