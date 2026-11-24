<?php

namespace App\Enums;

enum ChapterItemTypeEnum: string
{
    case LESSON = 'lesson';
    case QUIZ = 'quiz';
    case DOCUMENT = 'document';
    case LIVE = 'live';
}
