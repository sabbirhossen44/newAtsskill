<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\JitsiCredentialRequest;
use App\Http\Requests\Frontend\ZoomCredentialRequest;
use App\Repositories\LiveCredentialRepository;
use Illuminate\View\View;

class InstructorLiveCredentialController extends Controller
{
    use RedirectsWithMessage;

    function index(): View
    {
        $credential = LiveCredentialRepository::zoomCredentialFor(auth('web')->user());

        return view('frontend.instructor-dashboard.zoom.index', compact('credential'));
    }

    function update(ZoomCredentialRequest $request)
    {
        LiveCredentialRepository::updateZoom(auth('web')->user()->id, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value);
    }

    function jitsi_index(): View
    {
        $credential = LiveCredentialRepository::jitsiCredentialFor(auth('web')->user());

        return view('frontend.instructor-dashboard.jitsi.index', compact('credential'));
    }

    function jitsi_update(JitsiCredentialRequest $request)
    {
        LiveCredentialRepository::updateJitsi(auth('web')->user()->id, $request->validated(), $request->file('private_key'));

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value);
    }
}
