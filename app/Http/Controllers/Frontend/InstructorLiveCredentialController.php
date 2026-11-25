<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\JitsiCredentialRequest;
use App\Http\Requests\Frontend\ZoomCredentialRequest;
use App\Repositories\LiveCredentialRepository;
use Illuminate\View\View;

class InstructorLiveCredentialController extends Controller
{
    public function index(): View
    {
        $credential = LiveCredentialRepository::zoomCredentialFor(auth('web')->user());

        return view('frontend.instructor-dashboard.zoom.index', compact('credential'));
    }

    public function update(ZoomCredentialRequest $request)
    {
        LiveCredentialRepository::updateZoom(auth('web')->user()->id, $request->validated());

        return redirect()->back()->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function jitsi_index(): View
    {
        $credential = LiveCredentialRepository::jitsiCredentialFor(auth('web')->user());

        return view('frontend.instructor-dashboard.jitsi.index', compact('credential'));
    }

    public function jitsi_update(JitsiCredentialRequest $request)
    {
        LiveCredentialRepository::updateJitsi(auth('web')->user()->id, $request->validated(), $request->file('private_key'));

        return redirect()->back()->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }
}
