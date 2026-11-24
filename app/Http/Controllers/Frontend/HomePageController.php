<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\TeamMember;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\SendsMail;
use App\Models\Faq;
use App\Models\Blog;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Http\Controllers\Controller;
use App\Jobs\DefaultMailJob;
use App\Mail\DefaultMail;
use App\Models\City;
use App\Models\Country;
use App\Models\CustomPage;
use App\Models\EmailTemplate;
use App\Models\State;
use App\Models\HeroSection;
use App\Models\CourseCategory;
use App\Models\AboutSection;
use App\Models\BannerSection;
use App\Models\CounterSection;
use App\Models\Testimonial;
use App\Models\NewsletterSection;
use App\Models\FeaturedInstructor;
use App\Models\OurFeaturesSection;
use App\Models\SectionSetting;
use App\Models\FeaturedCourseSection;
use App\Models\FaqSection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class HomePageController extends Controller {
    use SendsMail;

    // ১. অপ্টিমাইজড ক্যাশসহ index মেথড
    function index(): View {
        $cacheDuration = 3600; // ক্যাash এর মেয়াদ (১ ঘণ্টা / ৩০০০ সেকেন্ড)। চাইলে কমাতে বা বাড়াতে পারেন।

        $hero = Cache::remember('home_hero_section', $cacheDuration, fn () => HeroSection::first());
        
        $trendingCategories = Cache::remember('home_trending_categories', $cacheDuration, function () {
            return CourseCategory::with(['translation:id,name,course_category_id', 'subCategories' => function ($query) {
                $query->withCount(['courses' => function ($query) {
                    $query->where('status', 'active');
                }]);
            }])->withCount(['subCategories as active_sub_categories_count' => function ($query) {
                $query->whereHas('courses', function ($query) {
                    $query->where('status', 'active');
                });
            }])->whereNull('parent_id')
                ->where('status', 1)
                ->where('show_at_trending', 1)
                ->get();
        });
            
        $brands = Cache::remember('home_brands', $cacheDuration, fn () => Brand::where('status', 1)->get());
        $aboutSection = Cache::remember('home_about_section', $cacheDuration, fn () => AboutSection::first());
        $featuredCourse = Cache::remember('home_featured_course_section', $cacheDuration, fn () => FeaturedCourseSection::first());
        $newsletterSection = Cache::remember('home_newsletter_section', $cacheDuration, fn () => NewsletterSection::first());
        $featuredInstructorSection = Cache::remember('home_featured_instructor_section', $cacheDuration, fn () => FeaturedInstructor::first());
        
        $selectedInstructors = Cache::remember('home_selected_instructors', $cacheDuration, function () {
            $usersInstructors = User::where(['role' => 'instructor', 'status' => 'active', 'is_banned' => 'no'])
                ->with(['courses' => function ($query) {
                    $query->withCount(['reviews as avg_rating' => function ($query) {
                        $query->select(DB::raw('coalesce(avg(rating),0)'));
                    }]);
                }])->get();

            $dbInstructors = DB::table('instructors')
                ->where('status', 1)
                ->get()
                ->map(function ($item) {
                    $item->job_title = $item->designation;
                    $item->courses = collect([]); 
                    return $item;
                });

            return $usersInstructors->concat($dbInstructors);
        });

        $counter = Cache::remember('home_counter_section', $cacheDuration, fn () => CounterSection::first());
        $faqSection = Cache::remember('home_faq_section', $cacheDuration, fn () => FaqSection::first());
        $faqs = Cache::remember('home_faqs', $cacheDuration, fn () => Faq::with('translation')->where('status', 1)->get());
        $ourFeatures = Cache::remember('home_our_features_section', $cacheDuration, fn () => OurFeaturesSection::first());
        $bannerSection = Cache::remember('home_banner_section', $cacheDuration, fn () => BannerSection::first());
        $testimonials = Cache::remember('home_testimonials', $cacheDuration, fn () => Testimonial::all());
        // Old scopeOrdered() is not in the Phase 1 TeamMember model; sort_order column exists.
        $teamMembers = Cache::remember('home_team_members', $cacheDuration, fn () => TeamMember::active()->orderBy('sort_order')->get());

        $featuredBlogs = Cache::remember('home_featured_blogs', $cacheDuration, function () {
            return Blog::with(['translation', 'author'])
                ->whereHas('category', function ($q) {$q->where('status', 1);})
                ->where(['show_homepage' => 1, 'status' => 1])->orderBy('created_at', 'desc')->limit(4)->get();
        });

        $sectionSetting = Cache::remember('home_section_setting', $cacheDuration, fn () => SectionSetting::first());

        $siteTheme = Session::has('demo_theme') ? Session::get('demo_theme') : (\App\Services\SettingService::all()['site_theme'] ?? 'theme-one');
        $themePath = ($siteTheme == 'theme-two') ? 'frontend.home-two.index' : 
                    (($siteTheme == 'theme-three') ? 'frontend.home-three.index' : 
                    (($siteTheme == 'theme-four') ? 'frontend.home-four.index' : 'frontend.home-one.index'));

        return view($themePath, compact(
            'hero', 'trendingCategories', 'brands', 'aboutSection', 'featuredCourse',
            'newsletterSection', 'featuredInstructorSection', 'selectedInstructors',
            'counter', 'faqSection', 'faqs', 'testimonials', 'teamMembers',
            'ourFeatures', 'bannerSection', 'featuredBlogs', 'sectionSetting'
        ));
    }

    // ২. allInstructors মেথড
    function allInstructors(Request $request) {
        $users = User::where(['status' => 'active', 'is_banned' => 0, 'role' => 'instructor'])->get();
        
        $newInstructors = DB::table('instructors')->where('status', 1)->get()->map(function($item) {
            $item->is_featured = $item->is_featured ?? 0;
            $item->job_title = $item->designation;
            return $item;
        });

        $allData = $users->concat($newInstructors);
        
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $items = $allData->forPage($currentPage, $perPage);
        
        $instructors = new LengthAwarePaginator(
            $items, 
            $allData->count(), 
            $perPage, 
            $currentPage, 
            ['path' => $request->url()]
        );

        return view('frontend.pages.instructors.index', compact('instructors'));
    }

    /**
     * The methods below are referenced by routes/web.php but were missing
     * from the old HomePageController. They are implemented here from the
     * old CurrencyController::setCurrency, the old helpers countries() and
     * the Location models, plus the instructor-details / quick-contact
     * behaviour implied by the old blade views.
     */
    function setCurrency($code)
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

    function countries()
    {
        return response()->json(Cache::rememberForever('countries', fn () => Country::all()));
    }

    function states($country_id)
    {
        return response()->json(State::where('country_id', $country_id)->get());
    }

    function cities($state_id)
    {
        return response()->json(City::where('state_id', $state_id)->get());
    }

    function instructorDetails($id, $slug = null)
    {
        $instructor = User::where(['id' => $id, 'status' => 'active', 'role' => 'instructor'])
            ->with(['courses' => function ($query) {
                $query->where(['is_approved' => 'approved', 'status' => 'active']);
            }])->first();

        if (! $instructor) {
            $instructor = DB::table('instructors')->where('id', $id)->where('status', 1)->first();
            if (! $instructor) {
                abort(404);
            }
            $instructor->job_title = $instructor->designation;
            $instructor->courses = collect([]);
        }

        return view('frontend.pages.instructors.show', compact('instructor'));
    }

    function quickConnect(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $instructor = User::findOrFail($id);

        try {
            self::setMailConfig();
            $template = EmailTemplate::where('name', 'instructor_quick_contact')->first();
            $subject = $template?->subject ?? $request->subject;
            $message = $template ? str_replace(
                ['{{name}}', '{{email}}', '{{subject}}', '{{message}}'],
                [$request->name, $request->email, $request->subject, $request->message],
                $template->message
            ) : $request->message;

            if (self::isQueable()) {
                DefaultMailJob::dispatch(['email' => $instructor->email, 'subject' => $subject], $message);
            } else {
                Mail::to($instructor->email)->send(new DefaultMail(['email' => $instructor->email, 'subject' => $subject], $message));
            }
        } catch (\Exception $e) {
            \Log::error('Quick connect mail error: '.$e->getMessage());
        }

        if (setting('google_tagmanager_status') === 'active') {
            session()->put('instructorQuickContact', [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);
        }

        return redirect()->back()->with(['messege' => __('Message sent successfully'), 'alert-type' => 'success']);
    }

    function changeTheme($name)
    {
        session()->put('demo_theme', $name);

        return redirect()->back();
    }

    function customPage($slug)
    {
        $page = CustomPage::where('slug', $slug)->firstOrFail();

        return view('frontend.pages.custom-page', compact('page'));
    }
}