<?php

namespace App\Repositories;

use App\Models\ContactMessage;
use App\Models\ContactSection;

class ContactMessageRepository
{
    public static function model()
    {
        return ContactMessage::class;
    }

    public static function contactSection(): ?ContactSection
    {
        return ContactSection::first();
    }

    public static function storeByRequest(array $data): ContactMessage
    {
        return static::model()::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);
    }
}
