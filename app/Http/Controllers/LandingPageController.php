<?php

namespace App\Http\Controllers;

use App\Http\Requests\Frontend\LandingPageRequest;
use App\Models\LandingPage;
use App\Repositories\LandingPageRepository;

class LandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPageRepository::all();

        return view('frontend.landing_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('frontend.landing_pages.create');
    }

    public function store(LandingPageRequest $request)
    {
        LandingPageRepository::storeByRequest($request->validated(), $request->file('hero_custom_thumbnail'));

        return redirect()->route('landing-pages.index')->with('success', 'Landing Page Created!');
    }

    public function show($slug)
    {
        $page = LandingPageRepository::findBySlugOrFail($slug);

        return view('frontend.landing_pages.show', compact('page'));
    }

    public function edit(LandingPage $landingPage)
    {
        return view('frontend.landing_pages.edit', compact('landingPage'));
    }

    public function update(LandingPageRequest $request, LandingPage $landingPage)
    {
        LandingPageRepository::updateByRequest($landingPage, $request->validated(), $request->file('hero_custom_thumbnail'));

        return redirect()->route('landing-pages.index')->with('success', 'Updated!');
    }

    public function destroy(LandingPage $landingPage)
    {
        LandingPageRepository::delete($landingPage);

        return redirect()->route('landing-pages.index')->with('success', 'Deleted!');
    }
}
