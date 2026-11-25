<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Requests\Global\CloudStorageRequest;
use App\Services\CloudStorageService;

class CloudStorageController extends Controller
{
    public function store(CloudStorageRequest $request)
    {
        if ($request->hasFile('file') && $request->filled('source')) {
            $path = CloudStorageService::store($request->file('file'), $request->source);

            if ($path) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('Uploaded successfully.'),
                    'path' => $path,
                ], 200);
            }

            return response()->json([
                'status' => 'failed',
                'message' => __('Upload failed'),
            ], 200);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('Upload failed'),
        ], 400);
    }
}
