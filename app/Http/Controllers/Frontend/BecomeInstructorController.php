<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BecomeInstructorStoreRequest;
use App\Repositories\InstructorRequestRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BecomeInstructorController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (InstructorRequestRepository::isApprovedInstructor(auth('web')->user())) {
            return to_route('instructor.dashboard');
        }

        $instructorRequestSetting = InstructorRequestRepository::firstSetting();
        $withdrawMethods = InstructorRequestRepository::activeWithdrawMethods();

        return view('frontend.pages.become-instructor', compact('withdrawMethods', 'instructorRequestSetting'));
    }

    public function store(BecomeInstructorStoreRequest $request): RedirectResponse
    {
        InstructorRequestRepository::submitByRequest(
            $request->validated(),
            auth('web')->user()->id,
            $request->file('certificate'),
            $request->file('identity_scan')
        );

        return redirect()->route('student.dashboard')->with([
            'success' => __('Instructor request submitted successfully we will let you know when your account is approved'),
            'alert-type' => 'success',
        ]);
    }
}
