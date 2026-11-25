<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailToNewsletterJob;
use App\Models\NewsLetter;
use App\Repositories\NewsLetterRepository;
use Illuminate\Http\Request;

/**
 * Newsletter subscribers (admin side). Sending mail dispatches the
 * existing SendMailToNewsletterJob to verified subscribers, as the
 * old controller did via MailSenderService.
 */
class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = NewsLetterRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));
        $newsletters = $subscribers;

        return view('admin.newsletter.index', compact('subscribers', 'newsletters'));
    }

    public function create()
    {
        return view('admin.newsletter.send-mail');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        dispatch(new SendMailToNewsletterJob($request->input('subject'), $request->input('description')));

        return redirect()->back()->with('success', __('Mail send successfully'));
    }

    public function destroy($id)
    {
        $subscriber = NewsLetterRepository::findByIdOrFail((int) $id);
        $subscriber->delete();

        return redirect()->back()->with('success', __('Deleted successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
