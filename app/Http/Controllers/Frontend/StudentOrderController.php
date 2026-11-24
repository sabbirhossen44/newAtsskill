<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class StudentOrderController extends Controller
{
    function index() {
        $orders = Order::where('buyer_id', auth('web')->user()->id)->orderBy('id', 'desc')->paginate(30);
        return view('frontend.student-dashboard.orders.index', compact('orders'));
    }

    function show(string $id) {
        $order = Order::where('id', $id)->where('buyer_id', auth('web')->user()->id)->firstOrFail();
        return view('frontend.student-dashboard.orders.show', compact('order'));
    }

    function printInvoice( Request $request, $id) {
        $order = Order::where('id', $id)->where('buyer_id', auth('web')->user()->id)->firstOrFail();
       return view('frontend.student-dashboard.orders.invoice', compact('order'));
    }
}
