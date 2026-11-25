<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
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
    use ChecksAdminPermissions;

    public function index(Request $request)
    {
        $this->checkAdminPermission('menu.view');
        $menus = Menu::all();
        $selected = $request->input('menu') ? Menu::find($request->input('menu')) : Menu::first();
        $menu = $selected;
        $menuItems = $selected ? MenuItem::where('menu_id', $selected->id)->orderBy('sort')->get() : collect();

        return view('admin.menu-builder.index', compact('menus', 'menu', 'menuItems'));
    }

    public function createMenu(Request $request)
    {
        $this->checkAdminPermission('menu.create');
        $request->validate(['name' => 'required|string|max:255']);
        $slug = Str::slug($request->input('name'));
        if (Menu::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::random(5);
        }
        $menu = MenuRepository::storeByRequest(['name' => $request->input('name'), 'slug' => $slug]);
        if (method_exists($menu, 'translations')) {
            TranslationService::sync($menu, 'translations', ['en' => ['name' => $request->input('name')]]);
        }

        return redirect()->back()->with(['messege' => __('Created Successfully'), 'alert-type' => 'success']);
    }

    public function updateMenu(Request $request)
    {
        $this->checkAdminPermission('menu.update');
        $menu = MenuRepository::findByIdOrFail((int) $request->input('id', $request->input('idmenu')));
        MenuRepository::updateByRequest($menu, ['name' => $request->input('name', $request->input('menuname'))]);

        return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
    }

    public function deleteMenu(Request $request)
    {
        $this->checkAdminPermission('menu.delete');
        $menu = MenuRepository::findByIdOrFail((int) $request->input('id'));
        if (MenuItem::where('menu_id', $menu->id)->exists()) {
            return redirect()->back()->with(['messege' => __('Delete all items first'), 'alert-type' => 'error']);
        }
        MenuRepository::delete($menu);

        return redirect()->back()->with(['messege' => __('Deleted Successfully'), 'alert-type' => 'success']);
    }

    public function addMenuItem(Request $request)
    {
        $this->checkAdminPermission('menu.create');
        $request->validate(['label' => 'required|string|max:255', 'url' => 'nullable|string|max:255']);
        $menu = Menu::first();
        MenuItem::create([
            'menu_id' => $request->input('menu_id', $menu?->id),
            'label' => $request->input('label'),
            'url' => $request->input('url'),
            'sort' => 0,
        ]);

        return redirect()->back()->with(['messege' => __('Created Successfully'), 'alert-type' => 'success']);
    }

    public function updateMenuItem(Request $request)
    {
        $this->checkAdminPermission('menu.update');
        $item = MenuItem::findOrFail((int) $request->input('id'));
        $item->update($request->only(['label', 'url']));

        return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
    }

    public function deleteMenuItem(Request $request)
    {
        $this->checkAdminPermission('menu.delete');
        MenuItem::findOrFail((int) $request->input('id'))->delete();

        return redirect()->back()->with(['messege' => __('Deleted Successfully'), 'alert-type' => 'success']);
    }
}
