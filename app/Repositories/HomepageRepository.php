<?php

namespace App\Repositories;

use App\Enums\CourseStatusEnum;
use App\Enums\UserBannedEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\AboutSection;
use App\Models\BannerSection;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\CounterSection;
use App\Models\Faq;
use App\Models\FaqSection;
use App\Models\FeaturedCourseSection;
use App\Models\FeaturedInstructor;
use App\Models\HeroSection;
use App\Models\NewsletterSection;
use App\Models\OurFeaturesSection;
use App\Models\SectionSetting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * All homepage / about-page read queries, including the 1-hour
 * Cache::remember blocks that used to live in HomePageController.
 */
class HomepageRepository
{
    protected const CACHE_DURATION = 3600;

    /**
     * Full view-data array for the homepage (theme-one..four) index.
     * Keys match the old controller's compact() exactly.
     */
    public static function forHomepage(): array
    {
        $cacheDuration = static::CACHE_DURATION;

        return [
            'hero' => Cache::remember('home_hero_section', $cacheDuration, fn () => HeroSection::first()),
            'trendingCategories' => CourseCategoryRepository::trending(),
            'brands' => Cache::remember('home_brands', $cacheDuration, fn () => Brand::where('status', 1)->get()),
            'aboutSection' => Cache::remember('home_about_section', $cacheDuration, fn () => AboutSection::first()),
            'featuredCourse' => Cache::remember('home_featured_course_section', $cacheDuration, fn () => FeaturedCourseSection::first()),
            'newsletterSection' => Cache::remember('home_newsletter_section', $cacheDuration, fn () => NewsletterSection::first()),
            'featuredInstructorSection' => Cache::remember('home_featured_instructor_section', $cacheDuration, fn () => FeaturedInstructor::first()),
            'selectedInstructors' => static::selectedInstructors(),
            'counter' => Cache::remember('home_counter_section', $cacheDuration, fn () => CounterSection::first()),
            'faqSection' => Cache::remember('home_faq_section', $cacheDuration, fn () => FaqSection::first()),
            'faqs' => Cache::remember('home_faqs', $cacheDuration, fn () => Faq::with('translation')->where('status', 1)->get()),
            'testimonials' => Cache::remember('home_testimonials', $cacheDuration, fn () => Testimonial::all()),
            'teamMembers' => Cache::remember('home_team_members', $cacheDuration, fn () => TeamMember::active()->orderBy('sort_order')->get()),
            'ourFeatures' => Cache::remember('home_our_features_section', $cacheDuration, fn () => OurFeaturesSection::first()),
            'bannerSection' => Cache::remember('home_banner_section', $cacheDuration, fn () => BannerSection::first()),
            'featuredBlogs' => Cache::remember('home_featured_blogs', $cacheDuration, function () {
                return Blog::with(['translation', 'author'])
                    ->whereHas('category', function ($q) { $q->where('status', 1); })
                    ->where(['show_homepage' => 1, 'status' => 1])->orderBy('created_at', 'desc')->limit(4)->get();
            }),
            'sectionSetting' => Cache::remember('home_section_setting', $cacheDuration, fn () => SectionSetting::first()),
        ];
    }

    /**
     * About-page view data. The old AboutPageController did NOT cache
     * these queries, so this stays uncached (unlike forHomepage()).
     */
    public static function forAboutPage(): array
    {
        return [
            'hero' => HeroSection::first(),
            'aboutSection' => AboutSection::first(),
            'counter' => CounterSection::first(),
            'ourFeatures' => OurFeaturesSection::first(),
            'newsletterSection' => NewsletterSection::first(),
            'brands' => Brand::where('status', 1)->get(),
            'reviews' => Testimonial::all(),
            'faqs' => Faq::with('translation')->where('status', 1)->get(),
            'faqSection' => FaqSection::first(),
        ];
    }

    /**
     * User instructors merged with legacy `instructors`-table rows,
     * exactly as the old controller built them (cached 1 hour).
     */
    public static function selectedInstructors(): Collection
    {
        return Cache::remember('home_selected_instructors', static::CACHE_DURATION, function () {
            $usersInstructors = User::where([
                'role' => UserRoleEnum::INSTRUCTOR->value,
                'status' => UserStatusEnum::ACTIVE->value,
                'is_banned' => UserBannedEnum::NO->value,
            ])->with(['courses' => function ($query) {
                $query->withCount(['reviews as avg_rating' => function ($query) {
                    $query->select(DB::raw('coalesce(avg(rating),0)'));
                }]);
            }])->get();

            $dbInstructors = InstructorRepository::active()->map(function ($item) {
                $item->courses = collect([]);

                return $item;
            });

            return $usersInstructors->concat($dbInstructors);
        });
    }

    /**
     * All instructors, manually paginated (the merged collection cannot
     * be paginated by the query builder). The `is_banned => 0` value is
     * preserved verbatim from the old controller.
     */
    public static function paginateAllInstructors(string $url, int $currentPage = 1): LengthAwarePaginator
    {
        $users = User::where(['status' => UserStatusEnum::ACTIVE->value, 'is_banned' => 0, 'role' => UserRoleEnum::INSTRUCTOR->value])->get();

        $newInstructors = InstructorRepository::active()->map(function ($item) {
            $item->is_featured = $item->is_featured ?? 0;

            return $item;
        });

        $allData = $users->concat($newInstructors);

        $perPage = 12;
        $items = $allData->forPage($currentPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            $allData->count(),
            $perPage,
            $currentPage,
            ['path' => $url]
        );
    }

    /**
     * Instructor details: an active user instructor with approved +
     * active courses, falling back to a legacy `instructors` row.
     * Returns null when neither exists (the controller aborts 404).
     */
    public static function findInstructorForDetails(int $id): mixed
    {
        $instructor = User::where([
            'id' => $id,
            'status' => UserStatusEnum::ACTIVE->value,
            'role' => UserRoleEnum::INSTRUCTOR->value,
        ])->with(['courses' => function ($query) {
            $query->where([
                'is_approved' => 'approved',
                'status' => CourseStatusEnum::ACTIVE->value,
            ]);
        }])->first();

        if ($instructor) {
            return $instructor;
        }

        $legacy = InstructorRepository::findActiveById($id);

        if (! $legacy) {
            return null;
        }

        $legacy->courses = collect([]);

        return $legacy;
    }
}
