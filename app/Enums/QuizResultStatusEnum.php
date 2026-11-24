<?php

namespace App\Enums;

enum QuizResultStatusEnum: string
{
    case PASS = 'pass';
    case FAILED = 'failed';
}
