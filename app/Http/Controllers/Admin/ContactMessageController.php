<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ContactMessageRepository;
use Illuminate\Pagination\Paginator;

/**
 * Contact messages (admin side): list, show, delete.
 */
class ContactMessageController extends Controller
{
    public function index()
    {
        Paginator::useBootstrap();
        $messages = ContactMessageRepository::paginateAdmin(null, $this->perPage(request()));

        return view('admin.contact-message.index', ['messages' => $messages, 'items' => $messages]);
    }

    public function show($id)
    {
        $message = ContactMessageRepository::findByIdOrFail((int) $id);
        $item = $message;

        return view('admin.contact-message.show', compact('message', 'item'));
    }

    public function destroy($id)
    {
        $message = ContactMessageRepository::findByIdOrFail((int) $id);
        $message->delete();

        return redirect()->route('admin.contact-messages')->with('success', __('Deleted successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
