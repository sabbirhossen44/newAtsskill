<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\TinymceImageUploadRequest;
use App\Services\TinymceImageService;
use Illuminate\Http\Request;

class TinymceImageUploadController extends Controller
{
    public function upload(TinymceImageUploadRequest $request)
    {
        // Upload the image using the file_upload helper function
        if ($request->hasFile('file')) {
            $url = TinymceImageService::store($request->file('file'));

            return response()->json(['location' => asset($url)]);
        }

        return response()->json(['error' => __('Image upload failed')], 422);
    }

    public function destroy(Request $request)
    {
        if (TinymceImageService::deleteByPath($request->input('file_path'))) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'File not found or path missing'], 400);
    }
}
