<?php

namespace App\Http\Controllers;

use App\Http\Requests\Frontend\BkashRefundRequest;
use App\Http\Requests\Frontend\BkashRefundStatusRequest;
use App\Services\BkashService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * bKash Tokenized Checkout controller (thin). The bKash HTTP flow,
 * order creation, enrolment and instructor wallet credit live in
 * BkashService / OrderRepository; config comes only from
 * PaymentService::gatewayConfig('bkash'). No SDK keys are hard-coded.
 */
class BkashTokenizePaymentController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        return view('frontend.pages.checkout.index', [
            'products' => $this->cart->content(),
            'payable_amount' => Session::get('payable_amount', 0),
        ]);
    }

    public function createPayment(Request $request)
    {
        $result = BkashService::createPayment($request->input('amount'), $this->cart->content()->toArray());

        if (isset($result['url'])) {
            return redirect()->away($result['url']);
        }

        return redirect()->back()->with('error-alert2', $result['error']);
    }

    public function callBack(Request $request)
    {
        $result = BkashService::handleCallback($request->status, $request->paymentID, auth('web')->user());

        if ($result['outcome'] === 'success') {
            return redirect()->route('order-success')->with('success', $result['message']);
        }

        return redirect()->route('order-fail')->with('error', $result['message']);
    }

    public function searchTnx($trxID)
    {
        $data = BkashService::searchTransaction($trxID);

        if ($data === null) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        return response()->json($data);
    }

    public function refund(BkashRefundRequest $request)
    {
        $data = BkashService::refund($request->validated());

        if ($data === null) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        return response()->json($data);
    }

    public function refundStatus(BkashRefundStatusRequest $request)
    {
        $data = BkashService::refundStatus($request->trxID);

        if ($data === null) {
            return response()->json(['message' => 'bKash is not configured'], 503);
        }

        return response()->json($data);
    }
}
