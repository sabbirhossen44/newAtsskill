<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Repositories\RefundRepository;
use Illuminate\Pagination\Paginator;

/**
 * Refunds. The rebuilt refund views are stubs ("Refund module is
 * disabled in this installation"), so index/show only render them;
 * the list is still served from RefundRequest rows for when the
 * module is re-enabled.
 */
class RefundController extends Controller
{
    use ChecksAdminPermissions;

    public function index()
    {
        $this->checkAdminPermission('order.management');
        Paginator::useBootstrap();
        $refund_requests = RefundRepository::paginateAdmin(null, 15);

        return view('admin.refund.index', ['refund_requests' => $refund_requests]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('order.management');
        $refund_request = RefundRepository::findByIdOrFail((int) $id);

        return view('admin.refund.show', ['refund_request' => $refund_request, 'item' => $refund_request]);
    }
}
