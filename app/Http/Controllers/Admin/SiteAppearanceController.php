<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Site appearance as key/value settings rows (there is no
 * site_appearances table in this rebuild; the old controller stored
 * the theme in `settings`). The rebuilt CRUD views manage the
 * appearance keys (`site_theme`, `show_all_homepage`, ...).
 */
class SiteAppearanceController extends Controller
{
    protected array $keys = ['site_theme', 'show_all_homepage'];

    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $items = Setting::query()->whereIn('key', $this->keys)->latest()->paginate($this->perPage($request));

        return view('admin.site-appearance.index', ['items' => $items]);
    }

    public function create()
    {
        return view('admin.site-appearance.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'value' => 'nullable|string']);
        SettingService::set($request->input('name'), $request->input('value', ''));

        return redirect()->route('admin.site-appearance.index')->with('success', 'Site appearance created successfully.');
    }

    public function edit($id)
    {
        $item = Setting::findOrFail($id);

        return view('admin.site-appearance.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['theme' => 'nullable|string', 'value' => 'nullable|string', 'name' => 'nullable|string']);
        $item = Setting::findOrFail($id);
        if ($request->filled('theme')) {
            SettingService::set('site_theme', $request->input('theme'));
        }
        $item->update([
            'key' => $request->input('name', $item->key),
            'value' => $request->input('value', $item->value),
        ]);
        SettingService::forgetCache();

        return redirect()->route('admin.site-appearance.index')->with('success', 'Site appearance updated successfully.');
    }

    public function destroy($id)
    {
        Setting::findOrFail($id)->delete();

        return redirect()->route('admin.site-appearance.index')->with('success', 'Site appearance deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
