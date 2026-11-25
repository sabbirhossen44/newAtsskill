<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\PayoutStoreRequest;
use App\Repositories\WithdrawRepository;

class InstructorPayoutController extends Controller
{
    function index()
    {
        $data = WithdrawRepository::payoutIndexData(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.payout.index', $data);
    }

    function create()
    {
        $data = WithdrawRepository::payoutCreateData(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.payout.create', $data);
    }

    function store(PayoutStoreRequest $request)
    {
        $user = auth('web')->user();

        if ($error = WithdrawRepository::validatePayout($user->id, $user->wallet_balance, $request->amount)) {
            return redirect()->back()->with(['alert-type' => 'error', 'messege' => $error]);
        }

        WithdrawRepository::storePayoutRequest($user->id, $user->wallet_balance, $request->amount);

        return redirect()->route('instructor.payout.index')->with(['alert-type' => 'success', 'messege' => __('Payout request sent successfully.')]);
    }

    function destroy(string $id)
    {
        if (WithdrawRepository::deletePending($id, auth('web')->user()->id)) {
            return response(['status' => 'success', 'message' => __('Deleted Successfully')]);
        }

        return response(['status' => 'success', 'message' => __('Request not found')]);
    }
}
