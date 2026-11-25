<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Http\Requests\Admin\LanguageRequest;
use App\Models\Language;
use App\Repositories\LanguageRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;

/**
 * Languages + static (JSON) translations. Creating a language copies
 * lang/en.json to lang/{code}.json, as the old controller did.
 * update() type-hints the Language model so LanguageRequest's
 * `$this->language->id` unique rules resolve.
 */
class LanguageController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('language.view');
        Paginator::useBootstrap();
        $languages = LanguageRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));

        return view('admin.language.index', ['languages' => $languages, 'items' => $languages]);
    }

    public function create()
    {
        $this->checkAdminPermission('language.create');

        return view('admin.language.create');
    }

    public function store(LanguageRequest $request)
    {
        $this->checkAdminPermission('language.store');
        $data = $request->validated();
        $data['direction'] = in_array($data['code'], ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
        $data['status'] = $request->input('status') === 'active' || $request->input('status') === 1 || $request->input('status') === '1' ? 'active' : 'inactive';
        $language = LanguageRepository::storeByRequest($data);

        $source = base_path('lang/en.json');
        $destination = base_path("lang/{$language->code}.json");
        if (File::exists($source) && ! File::exists($destination)) {
            File::put($destination, File::get($source));
        }

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.languages.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('language.edit');
        $item = LanguageRepository::findByIdOrFail((int) $id);

        return view('admin.language.edit', ['item' => $item, 'language' => $item]);
    }

    public function update(LanguageRequest $request, Language $language)
    {
        $this->checkAdminPermission('language.update');
        $data = $request->validated();
        if ($request->has('status')) {
            $data['status'] = $request->input('status') === 'active' || $request->input('status') === 1 || $request->input('status') === '1' ? 'active' : 'inactive';
        }
        LanguageRepository::updateByRequest($language, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.languages.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('language.delete');
        $language = LanguageRepository::findByIdOrFail((int) $id);
        LanguageRepository::delete($language);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.languages.index');
    }

    public function updateStatus(Request $request, Language $language)
    {
        $this->checkAdminPermission('language.update');
        LanguageRepository::updateByRequest($language, ['status' => $language->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }

    public function editStaticLanguages(Request $request, $code)
    {
        $this->checkAdminPermission('language.edit');
        $language = LanguageRepository::findByCode($code) ?? abort(404);
        $filePath = base_path("lang/{$code}.json");
        if (! File::exists($filePath)) {
            return redirect()->route('admin.languages.index')->with(['alert-type' => 'warning', 'messege' => __('Not Found!')]);
        }
        $translations = json_decode(File::get($filePath), true) ?? [];
        $languages = Language::all();

        return view('admin.language.edit-static-language', compact('translations', 'language', 'languages'));
    }

    public function updateStaticLanguages(Request $request, $code)
    {
        $this->checkAdminPermission('language.update');
        $filePath = base_path("lang/{$code}.json");
        if (! File::exists($filePath)) {
            return redirect()->route('admin.languages.index')->with(['alert-type' => 'warning', 'messege' => __('Not Found!')]);
        }
        $existing = json_decode(File::get($filePath), true) ?? [];
        $values = $request->input('values', []);
        foreach ($values as $key => $value) {
            $existing[$key] = $value;
        }
        File::put($filePath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.languages.index');
    }
}
