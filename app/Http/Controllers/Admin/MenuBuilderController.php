<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Repositories\MenuRepository;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Menu builder. The rebuilt view posts plain forms (name / label / url /
 * id) instead of the old AJAX payloads, so create/update/delete work
 * from those fields and redirect back. Menu names are translated via
 * the translations relation when present.
 */
class MenuBuilderController extends Controller
{
    public function index(Request $request)
    {
        $menus = Menu::all();
        $selected = $request->input('menu') ? Menu::find($request->input('menu')) : Menu::first();
        $menu = $selected;
        $menuItems = $selected ? MenuItem::where('menu_id', $selected->id)->orderBy('sort')->get() : collect();

        return view('admin.menu-builder.index', compact('menus', 'menu', 'menuItems'));
    }

    public function createMenu(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $slug = Str::slug($request->input('name'));
        if (Menu::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::random(5);
        }
        $menu = MenuRepository::storeByRequest(['name' => $request->input('name'), 'slug' => $slug]);
        if (method_exists($menu, 'translations')) {
            TranslationService::sync($menu, 'translations', ['en' => ['name' => $request->input('name')]]);
        }

        return redirect()->back()->with('success', __('Created Successfully'));
    }

    public function updateMenu(Request $request)
    {
        $menu = MenuRepository::findByIdOrFail((int) $request->input('id', $request->input('idmenu')));
        MenuRepository::updateByRequest($menu, ['name' => $request->input('name', $request->input('menuname'))]);

        return redirect()->back()->with('success', __('Updated Successfully'));
    }

    public function deleteMenu(Request $request)
    {
        $menu = MenuRepository::findByIdOrFail((int) $request->input('id'));
        if (MenuItem::where('menu_id', $menu->id)->exists()) {
            return redirect()->back()->with('error', __('Delete all items first'));
        }
        $menu->delete();

        return redirect()->back()->with('success', __('Deleted Successfully'));
    }

    public function addMenuItem(Request $request)
    {
        $request->validate(['label' => 'required|string|max:255', 'url' => 'nullable|string|max:255']);
        $menu = Menu::first();
        MenuItem::create([
            'menu_id' => $request->input('menu_id', $menu?->id),
            'label' => $request->input('label'),
            'url' => $request->input('url'),
            'sort' => 0,
        ]);

        return redirect()->back()->with('success', __('Created Successfully'));
    }

    public function updateMenuItem(Request $request)
    {
        $item = MenuItem::findOrFail((int) $request->input('id'));
        $item->update($request->only(['label', 'url']));

        return redirect()->back()->with('success', __('Updated Successfully'));
    }

    public function deleteMenuItem(Request $request)
    {
        MenuItem::findOrFail((int) $request->input('id'))->delete();

        return redirect()->back()->with('success', __('Deleted Successfully'));
    }
}
