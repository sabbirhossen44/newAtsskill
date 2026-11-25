<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\QuickConnectRequest;
use App\Repositories\HomepageRepository;
use App\Repositories\LocationRepository;
use App\Repositories\PageRepository;
use App\Repositories\UserRepository;
use App\Services\InstructorContactService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class HomePageController extends Controller
{
    public function index(): View
    {
        $siteTheme = Session::has('demo_theme') ? Session::get('demo_theme') : (SettingService::all()['site_theme'] ?? 'theme-one');
        $themePath = ($siteTheme == 'theme-two') ? 'frontend.home-two.index' :
                    (($siteTheme == 'theme-three') ? 'frontend.home-three.index' :
                    (($siteTheme == 'theme-four') ? 'frontend.home-four.index' : 'frontend.home-one.index'));

        return view($themePath, HomepageRepository::forHomepage());
    }

    public function allInstructors(Request $request)
    {
        $instructors = HomepageRepository::paginateAllInstructors(
            $request->url(),
            LengthAwarePaginator::resolveCurrentPage(),
            $this->perPage($request),
            $request->query()
        );

        return view('frontend.pages.instructors.index', compact('instructors'));
    }

    public function setCurrency($code)
    {
        $currency = allCurrencies()->where('currency_code', $code)->first();
        if (session()->has('currency_code')) {
            session()->forget('currency_code');
            session()->forget('currency_position');
            session()->forget('currency_icon');
            session()->forget('currency_rate');
        }
        if ($currency) {
            session()->put('currency_code', $currency->currency_code);
            session()->put('currency_position', $currency->currency_position);
            session()->put('currency_icon', $currency->currency_icon);
            session()->put('currency_rate', $currency->currency_rate);
        }

        return redirect()->back();
    }

    public function countries()
    {
        return response()->json(LocationRepository::countries());
    }

    public function states($country_id)
    {
        return response()->json(LocationRepository::statesByCountry((int) $country_id));
    }

    public function cities($state_id)
    {
        return response()->json(LocationRepository::citiesByState((int) $state_id));
    }

    public function instructorDetails($id, $slug = null)
    {
        $instructor = HomepageRepository::findInstructorForDetails((int) $id);

        if (! $instructor) {
            abort(404);
        }

        return view('frontend.pages.instructors.show', compact('instructor'));
    }

    public function quickConnect(QuickConnectRequest $request, $id)
    {
        $instructor = UserRepository::findOrFail((int) $id);

        InstructorContactService::sendQuickConnect($request->validated(), $instructor);

        if (setting('google_tagmanager_status') === 'active') {
            session()->put('instructorQuickContact', $request->validated());
        }

        return redirect()->back()->with(['messege' => __('Message sent successfully'), 'alert-type' => 'success']);
    }

    public function changeTheme($name)
    {
        session()->put('demo_theme', $name);

        return redirect()->back();
    }

    public function customPage($slug)
    {
        $page = PageRepository::findBySlugOrFail($slug);

        return view('frontend.pages.custom-page', compact('page'));
    }

    /**
     * Frontend listing page size. allSolution's admin perPage() whitelists
     * [10, 25, 50, 100] with default 15; the public instructors listing
     * has always shown 12 per page, so that size is kept here.
     * Protected (not private) only because the base Controller still
     * defines protected perPage(); flip to private perPage() once the
     * base method is removed per spec section 1.
     */
    private function perPage(Request $request, int $default = 12): int
    {
        return $default;
    }
}
