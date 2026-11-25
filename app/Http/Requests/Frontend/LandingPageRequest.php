<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPageRequest extends FormRequest
{
    public function rules(): array
    {
        $landingPage = $this->route('landing_page');

        return [
            'title' => 'required',
            'slug' => ['required', Rule::unique('landing_pages', 'slug')->ignore($landingPage?->id)],
            'seo_title' => ['nullable'],
            'seo_description' => ['nullable'],
            'status' => ['nullable'],
            // Content fields: unvalidated passthrough in the old controller,
            // listed here as nullable so validated() carries them through.
            'hero_title' => ['nullable'],
            'hero_subtitle' => ['nullable'],
            'hero_button_text' => ['nullable'],
            'hero_button_link' => ['nullable'],
            'hero_video_url' => ['nullable'],
            'hero_reviews' => ['nullable'],
            'features' => ['nullable'],
            'overview_title' => ['nullable'],
            'overview_description' => ['nullable'],
            'instructor_name' => ['nullable'],
            'instructor_bio' => ['nullable'],
            'instructor_image' => ['nullable'],
            'curriculum' => ['nullable'],
            'why_choose' => ['nullable'],
            'price' => ['nullable'],
            'discount_price' => ['nullable'],
            'price_button_text' => ['nullable'],
            'price_button_link' => ['nullable'],
            'testimonials' => ['nullable'],
            'faq' => ['nullable'],
            'final_cta_title' => ['nullable'],
            'final_cta_button_text' => ['nullable'],
            'final_cta_button_link' => ['nullable'],
        ];
    }
}
