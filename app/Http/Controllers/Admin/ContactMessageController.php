<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Repositories\ContactMessageRepository;
use Illuminate\Pagination\Paginator;

/**
 * Contact messages (admin side): list, show, delete.
 */
class ContactMessageController extends Controller
{
    use ChecksAdminPermissions;

    public function index()
    {
        $this->checkAdminPermission('contect.message.view');
        Paginator::useBootstrap();
        $messages = ContactMessageRepository::paginateAdmin(null, $this->perPage(request()));

        return view('admin.contact-message.index', ['messages' => $messages, 'items' => $messages]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('contect.message.view');
        $message = ContactMessageRepository::findByIdOrFail((int) $id);
        $item = $message;

        return view('admin.contact-message.show', compact('message', 'item'));
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('contect.message.delete');
        $message = ContactMessageRepository::findByIdOrFail((int) $id);
        ContactMessageRepository::delete($message);

        return redirect()->route('admin.contact-messages')->with(['messege' => __('Deleted successfully'), 'alert-type' => 'success']);
    }
}
