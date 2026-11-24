<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\SendsMail;
use App\Http\Controllers\Controller;
use App\Jobs\NewsletterVerifyJob;
use App\Mail\NewsletterVerifyMail;
use App\Models\EmailTemplate;
use App\Models\NewsLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Port of the old Modules\NewsLetter NewsLetterController.
 * The old public routes were newsletter-request (POST) and
 * newsletter-verification/{token} (GET); the new route names are
 * newsletter.subscribe and newsletter.verification.
 */
class NewsletterController extends Controller
{
    use SendsMail;

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:news_letters,email',
        ], [
            'email.required' => __('Email is required'),
            'email.unique' => __('Email already exist'),
        ]);

        $newsletter = new NewsLetter();
        $newsletter->email = $request->email;
        $newsletter->verify_token = Str::random(100);
        $newsletter->save();

        $this->sendVerifyMailToNewsletter($newsletter);

        return response()->json(['message' => __('A verification link has been send to your email, please verify it and getting our newsletter')]);
    }

    public function verify($token)
    {
        $newsletter = NewsLetter::where('verify_token', $token)->first();

        if ($newsletter) {
            $newsletter->verify_token = Str::random(100);
            $newsletter->status = 'verified';
            $newsletter->save();

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

    protected function sendVerifyMailToNewsletter(NewsLetter $newsletter): void
    {
        if (! static::setMailConfig()) {
            return;
        }

        try {
            if (static::isQueable()) {
                dispatch(new NewsletterVerifyJob($newsletter));
            } else {
                $template = EmailTemplate::where('name', 'subscribe_notification')->first();
                Mail::to($newsletter->email)->send(new NewsletterVerifyMail($newsletter, $template->subject, $template->message));
            }
        } catch (\Exception $e) {
            \Log::error('Newsletter verify mail error: '.$e->getMessage());
        }
    }
}
