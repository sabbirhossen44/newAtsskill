<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Site colours as key/value settings rows (the old controller stored
 * the seven colour keys in `settings`). The rebuilt CRUD views manage
 * those colour keys.
 */
class SiteColorSettingController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    protected array $keys = ['primary_color', 'secondary_color', 'common_color_one',
        'common_color_two', 'common_color_three', 'common_color_four', 'common_color_five'];

    public function index(Request $request)
    {
        $this->checkAdminPermission('appearance.management');
        Paginator::useBootstrap();
        $items = Setting::query()->whereIn('key', $this->keys)->latest()->paginate($this->perPage($request));

        return view('admin.site-color-setting.index', ['items' => $items]);
    }

    public function create()
    {
        $this->checkAdminPermission('appearance.management');

        return view('admin.site-color-setting.create');
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('appearance.management');
        $request->validate(['name' => 'required|string|max:255', 'value' => 'nullable|string']);
        SettingService::set($request->input('name'), $request->input('value', ''));

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.site-color-setting.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('appearance.management');
        $item = Setting::findOrFail($id);

        return view('admin.site-color-setting.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdminPermission('appearance.management');
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

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.site-color-setting.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('appearance.management');
        Setting::findOrFail($id)->delete();

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.site-color-setting.index');
    }
}
