<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\RefundRepository;

/**
 * Refunds. The rebuilt refund views are stubs ("Refund module is
 * disabled in this installation"), so index/show only render them;
 * the list is still served from RefundRequest rows for when the
 * module is re-enabled.
 */
class RefundController extends Controller
{
    public function index()
    {
        $refund_requests = RefundRepository::paginateAdmin(null, 15);

        return view('admin.refund.index', ['refund_requests' => $refund_requests]);
    }

    public function show($id)
    {
        $refund_request = RefundRepository::findByIdOrFail((int) $id);

        return view('admin.refund.show', ['refund_request' => $refund_request, 'item' => $refund_request]);
    }
}
