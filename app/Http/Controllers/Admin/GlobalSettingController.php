<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomCode;
use App\Models\CustomPagination;
use App\Models\MarketingSetting;
use App\Models\SeoSetting;
use App\Services\SettingService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Global / general / SEO / credential / marketing settings, cache &
 * database tools and the system-update upload. Old behaviour kept:
 * settings live in the key/value `settings` table (SettingService) plus
 * the dedicated SeoSetting / MarketingSetting / CustomCode rows.
 * Validation is deliberately lenient (nullable) because the rebuilt
 * settings forms only post the fields they render; the old code
 * required fields those forms do not have.
 */
class GlobalSettingController extends Controller
{
    public function general_setting()
    {
        $custom_paginations = CustomPagination::all();
        $all_timezones = \DateTimeZone::listIdentifiers();

        return view('admin.global-setting.settings.index', compact('custom_paginations', 'all_timezones'));
    }

    public function commission_setting()
    {
        $commission = (object) [
            'commission_rate' => setting('commission_rate'),
            'instructor_commission' => setting('instructor_commission'),
            'admin_commission' => setting('admin_commission'),
        ];

        return view('admin.global-setting.commission_setting', compact('commission'));
    }

    public function update_commission(Request $request)
    {
        $request->validate([
            'commission_rate' => 'nullable|numeric',
            'instructor_commission' => 'nullable|numeric',
            'admin_commission' => 'nullable|numeric',
        ]);

        if ($request->filled('admin_commission')) {
            SettingService::set('commission_rate', $request->admin_commission);
        } elseif ($request->filled('commission_rate')) {
            SettingService::set('commission_rate', $request->commission_rate);
        }
        foreach (['instructor_commission', 'admin_commission'] as $key) {
            if ($request->filled($key)) {
                SettingService::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_general_setting(Request $request)
    {
        $request->validate(['app_name' => 'required|string|max:255']);

        foreach (['app_name', 'timezone', 'is_queable', 'live_mail_send', 'site_address', 'site_email',
                  'header_topbar_status', 'header_social_status', 'cursor_dot_status', 'preloader_status',
                  'copyright_text', 'version'] as $key) {
            if ($request->has($key) && $request->input($key) !== null) {
                SettingService::set($key, $request->input($key));
            }
        }
        foreach (['logo' => 'logo', 'favicon' => 'favicon', 'preloader' => 'preloader'] as $field => $key) {
            if ($request->hasFile($field)) {
                SettingService::set($key, UploadService::store($request->file($field), 'uploads/custom-images/', setting($key)));
            }
        }
        Cache::forget('corn_working');

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_logo_favicon(Request $request)
    {
        foreach (['logo' => 'logo', 'favicon' => 'favicon', 'preloader' => 'preloader'] as $field => $key) {
            if ($request->hasFile($field)) {
                SettingService::set($key, UploadService::store($request->file($field), 'uploads/custom-images/', setting($key)));
            }
        }

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function seo_setting()
    {
        $pages = SeoSetting::all();
        $row = $pages->first();
        $seo = (object) array_merge($row ? $row->toArray() : [], [
            'meta_title' => $row->seo_title ?? setting('meta_title'),
            'meta_description' => $row->seo_description ?? setting('meta_description'),
            'meta_keywords' => setting('meta_keywords'),
        ]);

        return view('admin.global-setting.seo_setting', compact('pages', 'seo'));
    }

    public function update_seo_setting(Request $request, $id = null)
    {
        $request->validate([
            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $page = $id ? SeoSetting::find($id) : SeoSetting::first();
        $page = $page ?? new SeoSetting(['page_name' => 'home']);
        $page->seo_title = $request->input('seo_title') ?? $request->input('meta_title') ?? $page->seo_title;
        $page->seo_description = $request->input('seo_description') ?? $request->input('meta_description') ?? $page->seo_description;
        $page->save();

        if ($request->filled('meta_keywords')) {
            SettingService::set('meta_keywords', $request->meta_keywords);
        }
        if ($request->filled('meta_title')) {
            SettingService::set('meta_title', $request->meta_title);
        }
        if ($request->filled('meta_description')) {
            SettingService::set('meta_description', $request->meta_description);
        }

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function crediential_setting()
    {
        return view('admin.global-setting.credientials.index');
    }

    protected function saveCredentialKeys(Request $request, array $keys): void
    {
        foreach ($keys as $key) {
            if ($request->has($key) && $request->input($key) !== null) {
                SettingService::set($key, $request->input($key));
            }
        }
    }

    public function update_google_captcha(Request $request)
    {
        $this->saveCredentialKeys($request, ['recaptcha_site_key', 'recaptcha_secret_key', 'google_captcha_status', 'google_recaptcha_status']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_tawk_chat(Request $request)
    {
        $this->saveCredentialKeys($request, ['tawk_property_id', 'tawk_chat_status', 'tawk_widget_id']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_google_analytic(Request $request)
    {
        $this->saveCredentialKeys($request, ['google_analytics_id', 'google_analytic_status', 'google_tagmanager_id']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_facebook_pixel(Request $request)
    {
        $this->saveCredentialKeys($request, ['facebook_pixel_id', 'facebook_pixel_status']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_social_login(Request $request)
    {
        $this->saveCredentialKeys($request, ['google_client_id', 'google_client_secret', 'facebook_client_id',
            'facebook_client_secret', 'google_login_status', 'facebook_login_status']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function update_aws_cloud(Request $request)
    {
        $this->saveCredentialKeys($request, ['aws_bucket', 'aws_access_key', 'aws_secret_key', 'aws_region', 'aws_status']);

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    public function cache_clear()
    {
        return view('admin.global-setting.cache_clear');
    }

    public function cache_clear_confirm()
    {
        Artisan::call('optimize:clear');

        return redirect()->back()->with('success', __('Cache cleared successfully'));
    }

    public function database_clear()
    {
        return view('admin.global-setting.database_clear');
    }

    public function database_clear_success(Request $request)
    {
        if (! Hash::check($request->input('password', ''), auth('admin')->user()->password)) {
            return redirect()->back()->with('error', __('Passwords do not match.'));
        }
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        Artisan::call('optimize:clear');
        $this->deleteDirectoryContents(public_path('uploads/custom-images'));

        return redirect()->back()->with('success', __('Database Cleared Successfully'));
    }

    public function customCode($type = null)
    {
        $customCode = CustomCode::first();
        if (! $customCode) {
            $customCode = new CustomCode();
            $customCode->css = '/* write your css code here without the style tag */';
            $customCode->javascript = '//write your javascript here without the script tag';
            $customCode->header_javascript = '//write your javascript here without the script tag';
            $customCode->save();
        }

        $view = 'admin.global-setting.custom_code';
        if ($type && in_array($type, ['css', 'js'], true)) {
            $view = 'admin.global-setting.custom_code_'.$type;
        }

        return view($view, compact('customCode'));
    }

    public function customCodeUpdate(Request $request)
    {
        $validated = $request->validate([
            'css' => 'sometimes',
            'javascript' => 'sometimes',
            'header_javascript' => 'sometimes',
        ]);

        $customCode = CustomCode::firstOrNew();
        $customCode->fill($validated);
        $customCode->save();
        Cache::forget('customCode');

        return redirect()->back()->with('success', __('Updated Successfully'));
    }

    public function systemUpdate()
    {
        $zipLoaded = extension_loaded('zip');
        $updateAvailablity = null;
        $updateFileDetails = false;
        $files = false;
        $uploadFileSize = false;

        $zipFilePath = public_path('upload/update.zip');
        if (File::exists($zipFilePath)) {
            $updateFileDetails = true;
            $uploadFileSize = File::size($zipFilePath);
        }

        return view('admin.global-setting.auto-update', compact('updateAvailablity', 'updateFileDetails', 'uploadFileSize', 'files', 'zipLoaded'));
    }

    public function systemUpdateStore(Request $request)
    {
        $request->validate(['zip_file' => 'required|mimes:zip']);

        $zipFilePath = public_path('upload/update.zip');
        if (File::exists($zipFilePath)) {
            File::delete($zipFilePath);
        }
        $request->file('zip_file')->move(public_path('upload'), 'update.zip');

        return back();
    }

    public function marketing_setting()
    {
        $marketing = (object) [
            'data_layer' => MarketingSetting::where('key', 'data_layer')->first()?->value ?? setting('google_data_layer'),
        ];

        return view('admin.global-setting.marketings.index', compact('marketing'));
    }

    public function update_google_data_layer(Request $request)
    {
        MarketingSetting::updateOrCreate(['key' => 'data_layer'], ['value' => $request->input('data_layer', '')]);
        SettingService::set('google_data_layer', $request->input('data_layer', ''));

        return redirect()->back()->with('success', __('Update Successfully'));
    }

    protected function deleteDirectoryContents(string $path): void
    {
        if (! File::isDirectory($path)) {
            return;
        }
        foreach (File::files($path) as $file) {
            File::delete($file->getPathname());
        }
    }
}
