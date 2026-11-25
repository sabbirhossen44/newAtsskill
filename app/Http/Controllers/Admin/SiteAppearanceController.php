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
 * Site appearance as key/value settings rows (there is no
 * site_appearances table in this rebuild; the old controller stored
 * the theme in `settings`). The rebuilt CRUD views manage the
 * appearance keys (`site_theme`, `show_all_homepage`, ...).
 */
class SiteAppearanceController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    protected array $keys = ['site_theme', 'show_all_homepage'];

    public function index(Request $request)
    {
        $this->checkAdminPermission('appearance.management');
        Paginator::useBootstrap();
        $items = Setting::query()->whereIn('key', $this->keys)->latest()->paginate($this->perPage($request));

        return view('admin.site-appearance.index', ['items' => $items]);
    }

    public function create()
    {
        $this->checkAdminPermission('appearance.management');

        return view('admin.site-appearance.create');
    }

    public function store(Request $request)
    {
        $this->checkAdminPermission('appearance.management');
        $request->validate(['name' => 'required|string|max:255', 'value' => 'nullable|string']);
        SettingService::set($request->input('name'), $request->input('value', ''));

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.site-appearance.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('appearance.management');
        $item = Setting::findOrFail($id);

        return view('admin.site-appearance.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdminPermission('appearance.management');
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

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.site-appearance.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('appearance.management');
        Setting::findOrFail($id)->delete();

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.site-appearance.index');
    }
}
