<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Jobs\SendMailToNewsletterJob;
use App\Models\NewsLetter;
use App\Repositories\NewsLetterRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Newsletter subscribers (admin side). Sending mail dispatches the
 * existing SendMailToNewsletterJob to verified subscribers, as the
 * old controller did via MailSenderService.
 */
class NewsletterController extends Controller
{
    use ChecksAdminPermissions;

    public function index(Request $request)
    {
        $this->checkAdminPermission('newsletter.view');
        Paginator::useBootstrap();
        $subscribers = NewsLetterRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));
        $newsletters = $subscribers;

        return view('admin.newsletter.index', compact('subscribers', 'newsletters'));
    }

    public function create()
    {
        $this->checkAdminPermission('newsletter.mail');

        return view('admin.newsletter.send-mail');
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('newsletter.mail');
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        dispatch(new SendMailToNewsletterJob($request->input('subject'), $request->input('description')));

        return redirect()->back()->with(['messege' => __('Mail send successfully'), 'alert-type' => 'success']);
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('newsletter.delete');
        $subscriber = NewsLetterRepository::findByIdOrFail((int) $id);
        NewsLetterRepository::delete($subscriber);

        return redirect()->back()->with(['messege' => __('Deleted successfully'), 'alert-type' => 'success']);
    }
}
