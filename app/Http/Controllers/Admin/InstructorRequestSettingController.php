<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorRequestSetting;
use App\Repositories\InstructorRequestRepository;
use App\Services\TranslationService;
use Illuminate\Http\Request;

/**
 * Instructor-request settings (single row). The rebuilt form posts
 * `description` + a `status` checkbox (value `active` when checked).
 */
class InstructorRequestSettingController extends Controller
{
    public function index()
    {
        $setting = InstructorRequestRepository::firstSetting() ?? new InstructorRequestSetting();

        return view('admin.instructor-request-setting.index', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = InstructorRequestSetting::findOrFail($id ?: 1);
        $setting->update([
            'status' => $request->input('status') === 'active' ? 'active' : 'inactive',
        ]);
        if ($request->filled('description') && method_exists($setting, 'translations')) {
            TranslationService::sync($setting, 'translations', ['en' => ['description' => $request->input('description')]]);
        }

        return redirect()->back()->with('success', __('Updated Successfully'));
    }
}
