<?php

namespace App\Enums;

enum LessonStorageEnum: string
{
    case UPLOAD = 'upload';
    case YOUTUBE = 'youtube';
    case VIMEO = 'vimeo';
    case EXTERNAL_LINK = 'external_link';
    case GOOGLE_DRIVE = 'google_drive';
    case IFRAME = 'iframe';
    case AWS = 'aws';
    case LIVE = 'live';
}
