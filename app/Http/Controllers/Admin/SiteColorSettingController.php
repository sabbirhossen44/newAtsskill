<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;

/**
 * Site colours as key/value settings rows (the old controller stored
 * the seven colour keys in `settings`). The rebuilt CRUD views manage
 * those colour keys.
 */
class SiteColorSettingController extends Controller
{
    protected array $keys = ['primary_color', 'secondary_color', 'common_color_one',
        'common_color_two', 'common_color_three', 'common_color_four', 'common_color_five'];

    public function index(Request $request)
    {
        $items = Setting::query()->whereIn('key', $this->keys)->latest()->paginate($this->perPage($request));

        return view('admin.site-color-setting.index', ['items' => $items]);
    }

    public function create()
    {
        return view('admin.site-color-setting.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'value' => 'nullable|string']);
        SettingService::set($request->input('name'), $request->input('value', ''));

        return redirect()->route('admin.site-color-setting.index')->with('success', 'Site color setting created successfully.');
    }

    public function edit($id)
    {
        $item = Setting::findOrFail($id);

        return view('admin.site-color-setting.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'common_color_one' => 'nullable|string',
            'common_color_two' => 'nullable|string',
            'common_color_three' => 'nullable|string',
            'common_color_four' => 'nullable|string',
            'common_color_five' => 'nullable|string',
            'value' => 'nullable|string',
            'name' => 'nullable|string',
        ]);
        $item = Setting::findOrFail($id);
        foreach ($this->keys as $key) {
            if ($request->filled($key)) {
                SettingService::set($key, $request->input($key));
            }
        }
        $item->update([
            'key' => $request->input('name', $item->key),
            'value' => $request->input('value', $item->value),
        ]);
        SettingService::forgetCache();

        return redirect()->route('admin.site-color-setting.index')->with('success', 'Site color setting updated successfully.');
    }

    public function destroy($id)
    {
        Setting::findOrFail($id)->delete();

        return redirect()->route('admin.site-color-setting.index')->with('success', 'Site color setting deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
