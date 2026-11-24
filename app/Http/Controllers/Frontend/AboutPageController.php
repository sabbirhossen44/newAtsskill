<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Faq;
use App\Models\AboutSection;
use App\Models\CounterSection;
use App\Models\FaqSection;
use App\Models\HeroSection;
use App\Models\NewsletterSection;
use App\Models\OurFeaturesSection;
use App\Models\Testimonial;

class AboutPageController extends Controller
{
    function index()
    {
        $hero = HeroSection::first();
        $aboutSection = AboutSection::first();
        $counter = CounterSection::first();
        $ourFeatures = OurFeaturesSection::first();
        $newsletterSection = NewsletterSection::first();
        $brands = Brand::where('status', 1)->get();
        $reviews = Testimonial::all();
        $faqs = Faq::with('translation')->where('status', 1)->get();
        $faqSection = FaqSection::first();
        return view('frontend.pages.about', compact('aboutSection', 'counter', 'ourFeatures', 'newsletterSection', 'hero', 'brands', 'reviews', 'faqSection', 'faqs'));
    }
}
