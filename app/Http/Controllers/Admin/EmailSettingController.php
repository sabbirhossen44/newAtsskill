<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Mail configuration + email templates. Old behaviour kept, except the
 * rebuilt email-config form only posts the fields it renders, so the
 * update validates those as nullable instead of the old 8 required
 * fields. Secrets are stored via the settings table, never rendered.
 */
class EmailSettingController extends Controller
{
    public function email_config()
    {
        $templates = EmailTemplate::all();
        $emailConfig = (object) SettingService::all();

        return view('admin.global-setting.email.email_config', compact('templates', 'emailConfig'));
    }

    public function update_email_config(Request $request)
    {
        $request->validate([
            'mail_driver' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_sender_name' => 'nullable|string',
            'mail_sender_email' => 'nullable|string',
            'contact_message_receiver_mail' => 'nullable|string',
        ]);

        foreach (['mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
                  'mail_encryption', 'mail_sender_name', 'mail_sender_email',
                  'contact_message_receiver_mail'] as $key) {
            if ($request->has($key) && $request->input($key) !== null && $request->input($key) !== '') {
                SettingService::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function edit_email_template($id)
    {
        $template = EmailTemplate::findOrFail($id);

        $map = [
            'password_reset' => 'password_reset',
            'contact_mail' => 'contact_mail',
            'subscribe_notification' => 'subscribe_notification',
            'user_verification' => 'user_verification',
            'approved_refund' => 'refund_approval',
            'new_refund' => 'new_refund',
            'pending_wallet_payment' => 'pending_wallet_payment',
            'approved_withdraw' => 'approved_withdraw',
            'instructor_request_approved' => 'instructor_request_approved',
            'instructor_request_pending' => 'instructor_request_pending',
            'instructor_request_rejected' => 'instructor_request_rejected',
            'instructor_quick_contact' => 'instructor_quick_contact',
            'order_completed' => 'order_completed',
            'qna_reply_mail' => 'qna_reply_mail',
            'live_class_mail' => 'live_class_mail',
        ];

        $view = $map[$template->name] ?? null;
        if (! $view || ! view()->exists('admin.global-setting.email.template.'.$view)) {
            return redirect()->back()->with('error', __('Something went wrong'));
        }

        return view('admin.global-setting.email.template.'.$view, compact('template'));
    }

    public function update_email_template(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $template = EmailTemplate::find($id);
        if (! $template) {
            return redirect()->back()->with('error', __('Something went wrong'));
        }

        $template->subject = $request->subject;
        $template->message = $request->message;
        $template->save();

        return redirect()->route('admin.email-configuration')->with('success', __('Updated Successfully'));
    }

    public function test_mail_credentials()
    {
        try {
            Mail::raw('This is a test email', function ($message) {
                $message->to('info@atsskill.com')->subject('Test Email');
            });

            return redirect()->back()->with('success', __('Mail Send Successfully'));
        } catch (\Throwable) {
            return redirect()->back()->with('error', __('Invalid Mail Credentials'));
        }
    }
}
