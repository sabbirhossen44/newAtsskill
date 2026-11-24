<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\InstructorRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BecomeInstructorStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\InstructorRequest;
use App\Models\InstructorRequestSetting;
use App\Models\WithdrawMethod;
use App\Http\Controllers\Concerns\UploadsFiles;

class BecomeInstructorController extends Controller
{
    use UploadsFiles;

    function index(): View|RedirectResponse
    {
        if ($this->checkIfApproveInstructor()) return to_route('instructor.dashboard');

        $instructorRequestSetting = InstructorRequestSetting::first();
        $withdrawMethods = WithdrawMethod::where('status', 'active')->get();
        return view('frontend.pages.become-instructor', compact('withdrawMethods', 'instructorRequestSetting'));
    }

    function store(BecomeInstructorStoreRequest $request): RedirectResponse
    {
        $instructorRequest = InstructorRequest::updateOrCreate(
            ['user_id' => auth('web')->user()->id],
            [
                'status' => 'pending',
                'payout_account' => $request->payout_account,
                'payout_information' => $request->payout_information,
                'extra_information' => $request->extra_information,
            ]
        );

        if($request->has('certificate')) {
            $filePath = $this->uploadFile($request->certificate);
            $instructorRequest->certificate = $filePath;
            $instructorRequest->save();
        }

        if($request->has('identity_scan')) {
            $filePath = $this->uploadFile($request->identity_scan);
            $instructorRequest->identity_scan = $filePath;
            $instructorRequest->save();
        }

        return redirect()->route('student.dashboard')->with([
            'success' => __('Instructor request submitted successfully we will let you know when your account is approved'),
            'alert-type' => 'success'
        ]);
    }

    function checkIfApproveInstructor(): bool
    {
        $user = auth('web')->user();

        if (! $user || $user->role !== 'instructor') {
            return false;
        }

        // Old code compared the instructor record's status to UserStatus::APPROVED;
        // the new Instructor model casts status to boolean and the approval lives
        // on the InstructorRequest, so either an approved request or an active
        // instructor record counts as approved.
        return InstructorRequest::where('user_id', $user->id)
            ->where('status', InstructorRequestStatusEnum::APPROVED->value)->exists()
            || (bool) $user->instructorInfo?->status;
    }
}
