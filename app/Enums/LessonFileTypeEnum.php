<?php

namespace App\Enums;

enum LessonFileTypeEnum: string
{
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case PDF = 'pdf';
    case TXT = 'txt';
    case DOCX = 'docx';
    case IFRAME = 'iframe';
    case IMAGE = 'image';
    case FILE = 'file';
    case LIVE = 'live';
    case OTHER = 'other';
}
