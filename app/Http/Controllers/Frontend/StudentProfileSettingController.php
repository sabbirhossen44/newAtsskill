<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\RedirectTypeEnum;
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
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentProfileSettingController extends Controller
{
    use RedirectsWithMessage;

    function index(): View
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

    function updateProfile(StudentProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();

        UserRepository::updateProfileByRequest($user, $request->validated(), $request->file('image'), $request->file('cover'));

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function updateBio(StudentBioUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateBioByRequest(Auth::user(), $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function updatePassword(StudentPasswordUpdateRequest $request): RedirectResponse
    {
        UserRepository::updatePassword(Auth::user(), $request->validated()['password']);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function showExperienceModal(): string
    {
        return view('frontend.student-dashboard.profile.modals.experience-modal')->render();
    }

    function storeExperience(StudentExperienceStoreRequest $request): RedirectResponse
    {
        UserExperienceRepository::storeByRequest(Auth::user()->id, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'student.setting.index');
    }

    function editExperienceModal(string $id): string
    {
        $experience = UserExperienceRepository::findById((int) $id);

        return view('frontend.student-dashboard.profile.modals.edit-experience-modal', compact('experience'))->render();
    }

    function updateExperience(StudentExperienceStoreRequest $request, string $id): RedirectResponse
    {
        $experience = UserExperienceRepository::findForUserOrFail((int) $id, Auth::user()->id);
        UserExperienceRepository::updateByRequest($experience, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function destroyExperience(string $id): JsonResponse
    {
        setFormTabStep('profile_tab', 'education');

        UserExperienceRepository::deleteForUser((int) $id, Auth::user()->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted successfully'),
        ]);
    }

    function addEducationModal(): string
    {
        return view('frontend.student-dashboard.profile.modals.add-education-modal')->render();
    }

    function storeEducation(StudentEducationStoreRequest $request): RedirectResponse
    {
        UserEducationRepository::storeByRequest(Auth::user()->id, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'student.setting.index');
    }

    function editEducationModal(string $id): string
    {
        $education = UserEducationRepository::findById((int) $id);

        return view('frontend.student-dashboard.profile.modals.edit-education-modal', compact('education'))->render();
    }

    function updateEducation(StudentEducationStoreRequest $request, string $id): RedirectResponse
    {
        $education = UserEducationRepository::findForUserOrFail((int) $id, Auth::user()->id);
        UserEducationRepository::updateByRequest($education, $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function destroyEducation(string $id): JsonResponse
    {
        setFormTabStep('profile_tab', 'education');

        UserEducationRepository::deleteForUser((int) $id, Auth::user()->id);

        return response()->json([
            'status' => 'success',
            'message' => __('Deleted successfully'),
        ]);
    }

    function updateAddress(StudentProfileAddressUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateAddressByRequest(Auth::user(), $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }

    function updateSocials(StudentProfileSocialUpdateRequest $request): RedirectResponse
    {
        UserRepository::updateSocialsByRequest(Auth::user(), $request->validated());

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'student.setting.index');
    }
}
