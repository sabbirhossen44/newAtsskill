<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\NewsletterSubscribeRequest;
use App\Repositories\NewsLetterRepository;
use App\Services\NewsletterService;

/**
 * Port of the old Modules\NewsLetter NewsLetterController.
 * The old public routes were newsletter-request (POST) and
 * newsletter-verification/{token} (GET); the new route names are
 * newsletter.subscribe and newsletter.verification.
 */
class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request)
    {
        $newsletter = NewsLetterRepository::storeByRequest($request->validated());

        NewsletterService::sendVerificationMail($newsletter);

        return response()->json(['message' => __('A verification link has been send to your email, please verify it and getting our newsletter')]);
    }

    public function verify($token)
    {
        $newsletter = NewsLetterRepository::findByVerifyToken($token);

        if ($newsletter) {
            NewsLetterRepository::verify($newsletter);

            return redirect()->route('home')->with([
                'messege' => __('Newsletter verification successfully'),
                'alert-type' => 'success',
            ]);
        }

        return redirect()->route('home')->with([
            'messege' => __('Newsletter verification faild for invalid token'),
            'alert-type' => 'error',
        ]);
    }
}
