<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPage::all();
        return view('frontend.landing_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('frontend.landing_pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'slug'  => 'required|unique:landing_pages,slug',
        ]);

        // buildContent এ file upload handle হবে
        $content = $this->buildContent($request);

        LandingPage::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'status' => $request->status,
            'content' => $content,
        ]);

        return redirect()->route('landing-pages.index')->with('success', 'Landing Page Created!');
    }

    public function show($slug)
    {
        $page = LandingPage::where('slug', $slug)->firstOrFail();
        return view('frontend.landing_pages.show', compact('page'));
    }

    public function edit(LandingPage $landingPage)
    {
        return view('frontend.landing_pages.edit', compact('landingPage'));
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $request->validate([
            'title' => 'required',
            'slug'  => 'required|unique:landing_pages,slug,' . $landingPage->id,
        ]);

        $content = $this->buildContent($request, $landingPage);

        $landingPage->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'status' => $request->status,
            'content' => $content,
        ]);

        return redirect()->route('landing-pages.index')->with('success', 'Updated!');
    }

    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();
        return redirect()->route('landing-pages.index')->with('success', 'Deleted!');
    }

    private function buildContent(Request $request, LandingPage $landingPage = null)
    {
        // পুরোনো thumbnail যদি থাকে সেটাকে ধরে রাখব
        $heroThumbnailPath = $landingPage->content['hero']['custom_thumbnail'] ?? null;

        if ($request->hasFile('hero_custom_thumbnail')) {
            $file = $request->file('hero_custom_thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();

            // file কে সরাসরি public/images/thumbnails ফোল্ডারে move করব
            $file->move(public_path('images/thumbnails'), $filename);

            // DB তে save করার জন্য relative path
            $heroThumbnailPath = 'images/thumbnails/' . $filename;
        }

        return [
            'hero' => [
                'title' => $request->hero_title,
                'subtitle' => $request->hero_subtitle,
                'button_text' => $request->hero_button_text,
                'button_link' => $request->hero_button_link,
                'video_url' => $request->hero_video_url,
                'custom_thumbnail' => $heroThumbnailPath,
                'reviews' => $request->hero_reviews ?? [],
            ],
            'features' => $request->features ?? [],
            'overview' => [
                'title' => $request->overview_title,
                'description' => $request->overview_description,
            ],
            'instructor' => [
                'name' => $request->instructor_name,
                'bio' => $request->instructor_bio,
                'image' => $request->instructor_image,
            ],
            'curriculum' => $request->curriculum ?? [],
            'why_choose' => $request->why_choose ?? [],
            'pricing' => [
                'price' => $request->price,
                'discount_price' => $request->discount_price,
                'button_text' => $request->price_button_text,
                'button_link' => $request->price_button_link,
            ],
            'testimonials' => $request->testimonials ?? [],
            'faq' => $request->faq ?? [],
            'final_cta' => [
                'title' => $request->final_cta_title,
                'button_text' => $request->final_cta_button_text,
                'button_link' => $request->final_cta_button_link,
            ],
        ];
    }
}
