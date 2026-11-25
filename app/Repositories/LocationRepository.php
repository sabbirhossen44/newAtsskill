<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LocationRepository
{
    /**
     * Homepage location dropdowns (no status filter, exactly as the old
     * HomePageController queried them). statesForCountry()/citiesForState()
     * below are the status-filtered variants used elsewhere.
     */
    public static function countries(): Collection
    {
        return Cache::rememberForever('countries', fn () => Country::all());
    }

    public static function statesByCountry(int $countryId): Collection
    {
        return State::query()->where('country_id', $countryId)->get();
    }

    public static function citiesByState(int $stateId): Collection
    {
        return City::query()->where('state_id', $stateId)->get();
    }

    public static function statesForCountry(?int $countryId): Collection
    {
        return State::query()
            ->where(['country_id' => $countryId, 'status' => 1])
            ->get();
    }

    public static function citiesForState(?int $stateId): Collection
    {
        return City::query()
            ->where(['state_id' => $stateId, 'status' => 1])
            ->get();
    }
}
