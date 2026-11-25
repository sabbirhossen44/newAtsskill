<?php

namespace App\Repositories;

use App\Models\LandingPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class LandingPageRepository
{
    public static function model()
    {
        return LandingPage::class;
    }

    public static function all(): Collection
    {
        return static::model()::all();
    }

    public static function findBySlugOrFail(string $slug): LandingPage
    {
        return static::model()::query()->where('slug', $slug)->firstOrFail();
    }

    public static function storeByRequest(array $data, ?UploadedFile $thumbnail = null): LandingPage
    {
        return static::model()::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'status' => $data['status'] ?? null,
            'content' => static::buildContent($data, null, $thumbnail),
        ]);
    }

    public static function updateByRequest(LandingPage $landingPage, array $data, ?UploadedFile $thumbnail = null): bool
    {
        return $landingPage->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'status' => $data['status'] ?? null,
            'content' => static::buildContent($data, $landingPage, $thumbnail),
        ]);
    }

    public static function delete(LandingPage $landingPage): bool
    {
        return (bool) $landingPage->delete();
    }

    /**
     * Content assembly moved verbatim from the old controller's
     * private buildContent(), including the hero-thumbnail upload
     * (stored under public/images/thumbnails/) and keeping the
     * previous thumbnail when no new file is uploaded.
     */
    public static function buildContent(array $data, ?LandingPage $landingPage, ?UploadedFile $thumbnail): array
    {
        $heroThumbnailPath = $landingPage?->content['hero']['custom_thumbnail'] ?? null;

        if ($thumbnail) {
            $filename = time().'_'.$thumbnail->getClientOriginalName();
            $thumbnail->move(public_path('images/thumbnails'), $filename);
            $heroThumbnailPath = 'images/thumbnails/'.$filename;
        }

        return [
            'hero' => [
                'title' => $data['hero_title'] ?? null,
                'subtitle' => $data['hero_subtitle'] ?? null,
                'button_text' => $data['hero_button_text'] ?? null,
                'button_link' => $data['hero_button_link'] ?? null,
                'video_url' => $data['hero_video_url'] ?? null,
                'custom_thumbnail' => $heroThumbnailPath,
                'reviews' => $data['hero_reviews'] ?? [],
            ],
            'features' => $data['features'] ?? [],
            'overview' => [
                'title' => $data['overview_title'] ?? null,
                'description' => $data['overview_description'] ?? null,
            ],
            'instructor' => [
                'name' => $data['instructor_name'] ?? null,
                'bio' => $data['instructor_bio'] ?? null,
                'image' => $data['instructor_image'] ?? null,
            ],
            'curriculum' => $data['curriculum'] ?? [],
            'why_choose' => $data['why_choose'] ?? [],
            'pricing' => [
                'price' => $data['price'] ?? null,
                'discount_price' => $data['discount_price'] ?? null,
                'button_text' => $data['price_button_text'] ?? null,
                'button_link' => $data['price_button_link'] ?? null,
            ],
            'testimonials' => $data['testimonials'] ?? [],
            'faq' => $data['faq'] ?? [],
            'final_cta' => [
                'title' => $data['final_cta_title'] ?? null,
                'button_text' => $data['final_cta_button_text'] ?? null,
                'button_link' => $data['final_cta_button_link'] ?? null,
            ],
        ];
    }
}
