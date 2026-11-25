<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;

class StudentOrderController extends Controller
{
    function index()
    {
        $orders = OrderRepository::paginateForBuyer(auth('web')->user()->id, 30);

        return view('frontend.student-dashboard.orders.index', compact('orders'));
    }

    function show(string $id)
    {
        $order = OrderRepository::findForBuyerOrFail((int) $id, auth('web')->user()->id);

        return view('frontend.student-dashboard.orders.show', compact('order'));
    }

    function printInvoice(Request $request, $id)
    {
        $order = OrderRepository::findForBuyerOrFail((int) $id, auth('web')->user()->id);

        return view('frontend.student-dashboard.orders.invoice', compact('order'));
    }
}
