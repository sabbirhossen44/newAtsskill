<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\HomepageRepository;

class AboutPageController extends Controller
{
    function index()
    {
        return view('frontend.pages.about', HomepageRepository::forAboutPage());
    }
}
