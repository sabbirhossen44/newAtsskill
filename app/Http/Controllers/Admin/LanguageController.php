<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $languages = LanguageRepository::paginateAdmin($request->input('keyword'), $this->perPage($request));

        return view('admin.language.index', ['languages' => $languages, 'items' => $languages]);
    }

    public function create()
    {
        return view('admin.language.create');
    }

    public function store(LanguageRequest $request)
    {
        $data = $request->validated();
        $data['direction'] = in_array($data['code'], ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
        $data['status'] = $request->input('status') === 'active' || $request->input('status') === 1 || $request->input('status') === '1' ? 'active' : 'inactive';
        $language = LanguageRepository::storeByRequest($data);

        $source = base_path('lang/en.json');
        $destination = base_path("lang/{$language->code}.json");
        if (File::exists($source) && ! File::exists($destination)) {
            File::put($destination, File::get($source));
        }

        return redirect()->route('admin.languages.index')->with('success', 'Language created successfully.');
    }

    public function edit($id)
    {
        $item = LanguageRepository::findByIdOrFail((int) $id);

        return view('admin.language.edit', ['item' => $item, 'language' => $item]);
    }

    public function update(LanguageRequest $request, Language $language)
    {
        $data = $request->validated();
        if ($request->has('status')) {
            $data['status'] = $request->input('status') === 'active' || $request->input('status') === 1 || $request->input('status') === '1' ? 'active' : 'inactive';
        }
        LanguageRepository::updateByRequest($language, $data);

        return redirect()->route('admin.languages.index')->with('success', 'Language updated successfully.');
    }

    public function destroy($id)
    {
        $language = LanguageRepository::findByIdOrFail((int) $id);
        $language->delete();

        return redirect()->route('admin.languages.index')->with('success', 'Language deleted successfully.');
    }

    public function updateStatus(Request $request, Language $language)
    {
        LanguageRepository::updateByRequest($language, ['status' => $language->status === 'active' ? 'inactive' : 'active']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
        }

        return back()->with('success', __('Updated Successfully'));
    }

    public function editStaticLanguages(Request $request, $code)
    {
        $language = LanguageRepository::findByCode($code) ?? abort(404);
        $filePath = base_path("lang/{$code}.json");
        if (! File::exists($filePath)) {
            return redirect()->route('admin.languages.index')->with('error', __('Not Found!'));
        }
        $translations = json_decode(File::get($filePath), true) ?? [];
        $languages = Language::all();

        return view('admin.language.edit-static-language', compact('translations', 'language', 'languages'));
    }

    public function updateStaticLanguages(Request $request, $code)
    {
        $filePath = base_path("lang/{$code}.json");
        if (! File::exists($filePath)) {
            return redirect()->route('admin.languages.index')->with('error', __('Not Found!'));
        }
        $existing = json_decode(File::get($filePath), true) ?? [];
        $values = $request->input('values', []);
        foreach ($values as $key => $value) {
            $existing[$key] = $value;
        }
        File::put($filePath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.languages.index')->with('success', 'Language updated successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
