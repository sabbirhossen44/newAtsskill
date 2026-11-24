<?php

namespace App\Services;

use App\Models\CertificateBuilder;
use App\Models\Course;
use App\Models\User;

class CertificateService
{
    public static function builder(): ?CertificateBuilder
    {
        return CertificateBuilder::query()->first();
    }

    public static function buildHtml(Course $course, User $user): string
    {
        $builder = static::builder();

        $title = $builder?->title ?? config('app.name');
        $subTitle = $builder?->sub_title ?? '';
        $description = $builder?->description ?? '';

        $items = $builder?->builderItems() ?? collect();

        $positions = $items->mapWithKeys(fn ($item) => [
            $item->element_id => ['x' => $item->x_position, 'y' => $item->y_position],
        ])->all();

        // Positions are percentages of the certificate canvas; the
        // Blade/PDF view reads $positions[$elementId] per element.
        return view('certificates.certificate', [
            'course' => $course,
            'user' => $user,
            'builder' => $builder,
            'title' => $title,
            'subTitle' => $subTitle,
            'description' => $description,
            'positions' => $positions,
        ])->render();
    }

    /**
     * Render the certificate as a PDF when barryvdh/laravel-dompdf
     * is installed; otherwise return the HTML so the caller can
     * still preview it. (dompdf is not a required dependency yet.)
     */
    public static function download(Course $course, User $user): string
    {
        $html = static::buildHtml($course, $user);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)
                ->setPaper('a4', 'landscape')
                ->output();
        }

        return $html;
    }
}
