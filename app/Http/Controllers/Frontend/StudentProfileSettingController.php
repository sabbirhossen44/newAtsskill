<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StudentBioUpdateRequest;
use App\Http\Requests\Frontend\StudentEducationStoreRequest;
use App\Http\Requests\Frontend\StudentExperienceStoreRequest;
use App\Http\Requests\Frontend\StudentPasswordUpdateRequest;
use App\Http\Requests\Frontend\StudentProfileUpdateRequest;
use App\Http\Requests\Frontend\StudentProfileAddressUpdateRequest;
use App\Http\Requests\Frontend\StudentProfileSocialUpdateRequest;
use App\Repositories\LocationRepository;
use App\Repositories\UserEducationRepository;
use App\Repositories\UserExperienceRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentProfileSettingController extends Controller
{
    public function index(): View
    {
        // set profile tab
        if (! session('profile_tab')) {
            setFormTabStep('profile_tab', 'profile');
        }

        $user = Auth::user();
        $experiences = UserExperienceRepository::allForUser($user->id);
        $educations = UserEducationRepository::allForUser($user->id);
        $states = LocationRepository::statesForCountry($user->country_id);
        $cities = LocationRepository::citiesForState($user->state_id);

        return view('frontend.student-dashboard.profile.index', compact('user', 'experiences', 'educations', 'states', 'cities'));
    }

    public function updateProfile(StudentProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();

        UserRepository::updateProfileByRequest($user, $request->validated(), $request->file('image'), $request->file('cover'));

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function updateBio(StudentBioUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateBioByRequest(Auth::user(), $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function updatePassword(StudentPasswordUpdateRequest $request): RedirectResponse
    {
        UserRepository::updatePassword(Auth::user(), $request->validated()['password']);

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function showExperienceModal(): string
    {
        return view('frontend.student-dashboard.profile.modals.experience-modal')->render();
    }

    public function storeExperience(StudentExperienceStoreRequest $request): RedirectResponse
    {
        UserExperienceRepository::storeByRequest(Auth::user()->id, $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Created successfully'), 'alert-type' => 'success']);
    }

    public function editExperienceModal(string $id): string
    {
        $experience = UserExperienceRepository::findById((int) $id);

        return view('frontend.student-dashboard.profile.modals.edit-experience-modal', compact('experience'))->render();
    }

    public function updateExperience(StudentExperienceStoreRequest $request, string $id): RedirectResponse
    {
        $experience = UserExperienceRepository::findForUserOrFail((int) $id, Auth::user()->id);
        UserExperienceRepository::updateByRequest($experience, $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function destroyExperience(string $id): JsonResponse
    {
        setFormTabStep('profile_tab', 'education');

        UserExperienceRepository::deleteForUser((int) $id, Auth::user()->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted successfully'),
        ]);
    }

    public function addEducationModal(): string
    {
        return view('frontend.student-dashboard.profile.modals.add-education-modal')->render();
    }

    public function storeEducation(StudentEducationStoreRequest $request): RedirectResponse
    {
        UserEducationRepository::storeByRequest(Auth::user()->id, $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Created successfully'), 'alert-type' => 'success']);
    }

    public function editEducationModal(string $id): string
    {
        $education = UserEducationRepository::findById((int) $id);

        return view('frontend.student-dashboard.profile.modals.edit-education-modal', compact('education'))->render();
    }

    public function updateEducation(StudentEducationStoreRequest $request, string $id): RedirectResponse
    {
        $education = UserEducationRepository::findForUserOrFail((int) $id, Auth::user()->id);
        UserEducationRepository::updateByRequest($education, $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function destroyEducation(string $id): JsonResponse
    {
        setFormTabStep('profile_tab', 'education');

        UserEducationRepository::deleteForUser((int) $id, Auth::user()->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted successfully'),
        ]);
    }

    public function updateAddress(StudentProfileAddressUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateAddressByRequest(Auth::user(), $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function updateSocials(StudentProfileSocialUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateSocialsByRequest(Auth::user(), $request->validated());

        return redirect()->route('student.setting.index')->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }
}
