<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\User;

/**
 * Instructor profile-settings page data (moved from
 * InstructorProfileSettingController::index). Profile / bio / password /
 * address / socials writes live in UserRepository; experience and
 * education writes live in their own repositories.
 */
class InstructorProfileRepository extends Repository
{
    public static function model()
    {
        return User::class;
    }

    public static function profileContext(User $user): array
    {
        $experiences = UserExperienceRepository::allForUser($user->id);
        $educations = UserEducationRepository::allForUser($user->id);
        $states = LocationRepository::statesForCountry($user->country_id);
        $cities = LocationRepository::citiesForState($user->state_id);
        $payoutInfo = InstructorRequestRepository::firstForUser($user->id);
        $instructorRequestSetting = InstructorRequestRepository::firstSetting();
        $withdrawMethods = InstructorRequestRepository::activeWithdrawMethods();
        $instructorRequest = InstructorRequestRepository::firstForUser($user->id);

        return compact('user', 'experiences', 'educations', 'states', 'cities', 'payoutInfo', 'instructorRequestSetting', 'withdrawMethods', 'instructorRequest');
    }
}
