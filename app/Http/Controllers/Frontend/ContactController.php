<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ContactSendMailRequest;
use App\Repositories\ContactMessageRepository;
use App\Services\ContactMessageService;

class ContactController extends Controller
{
    public function index()
    {
        $contact = ContactMessageRepository::contactSection();

        return view('frontend.pages.contact', compact('contact'));
    }

    public function sendMail(ContactSendMailRequest $request)
    {
        $validated = $request->validated();

        $contact = ContactMessageRepository::storeByRequest($validated);

        ContactMessageService::send($validated, $contact);

        return response()->json(['success' => true, 'message' => 'message sent successfully'], 200);
    }
}
