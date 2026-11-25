<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Homepage section toggles. The old controller kept a single
 * SectionSetting row of boolean flags; the rebuilt CRUD views list
 * one row per flag, so index/edit expose each flag as a pseudo-row
 * (id = flag index, name = column, status = active/inactive).
 */
class SectionSettingController extends Controller
{
    protected function flags(): array
    {
        return ['hero_section', 'top_category_section', 'brands_section', 'about_section',
            'featured_course_section', 'news_letter_section', 'featured_instructor_section',
            'counter_section', 'faq_section', 'our_features_section', 'testimonial_section',
            'banner_section', 'latest_blog_section', 'blog_page', 'about_page', 'contact_page'];
    }

    protected function rows(): Collection
    {
        $row = SectionSetting::first();
        $items = collect();
        foreach ($this->flags() as $i => $flag) {
            $items->push((object) [
                'id' => $i + 1,
                'name' => $flag,
                'status' => $row && $row->{$flag} ? 'active' : 'inactive',
                'value' => $row && $row->{$flag} ? '1' : '0',
            ]);
        }

        return $items;
    }

    public function index()
    {
        $items = $this->rows();

        return view('admin.section-setting.index', ['items' => $items]);
    }

    public function create()
    {
        return view('admin.section-setting.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $flag = $request->input('name');
        if (in_array($flag, $this->flags(), true)) {
            SectionSetting::updateOrCreate(['id' => 1], [$flag => $request->input('status') === 'active' ? 1 : 0]);
        }

        return redirect()->route('admin.section-setting.index')->with('success', 'Section setting created successfully.');
    }

    public function edit($id)
    {
        $item = $this->rows()->firstWhere('id', (int) $id) ?? abort(404);

        return view('admin.section-setting.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->rows()->firstWhere('id', (int) $id) ?? abort(404);
        SectionSetting::updateOrCreate(['id' => 1], [$item->name => $request->input('status') === 'active' || $request->boolean('value') ? 1 : 0]);

        return redirect()->route('admin.section-setting.index')->with('success', 'Section setting updated successfully.');
    }

    public function destroy($id)
    {
        $item = $this->rows()->firstWhere('id', (int) $id) ?? abort(404);
        SectionSetting::updateOrCreate(['id' => 1], [$item->name => 0]);

        return redirect()->route('admin.section-setting.index')->with('success', 'Section setting deleted successfully.');
    }
}
