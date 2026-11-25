<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BadgeRequest;
use App\Repositories\BadgeRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;

class BadgesController extends Controller
{
    public function index(Request $request)
    {
        $badges = BadgeRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.badges.index', ['badges' => $badges, 'items' => $badges]);
    }

    public function create()
    {
        return view('admin.badges.create', []);
    }

    public function store(BadgeRequest $request)
    {
        $data = $request->validated();
        $data['name'] = $data['title'] ?? $data['name'] ?? null;
                $data['key'] = $data['key'] ?? \Illuminate\Support\Str::slug($data['name'] ?? 'badge');
                unset($data['title'], $data['color']);
        if ($request->hasFile('icon')) {
            $data['image'] = UploadService::store($request->file('icon'));
        }
        // badges.image is NOT NULL; the old MySQL code relied on non-strict
        // mode inserting '' when no icon was uploaded.
        $data['image'] = $data['image'] ?? '';

        BadgeRepository::storeByRequest($data);

        return redirect()->route('admin.badges.index')->with('success', 'Badge created successfully.');
    }

    public function edit($id)
    {
        $item = BadgeRepository::findByIdOrFail($id);
        return view('admin.badges.edit', ['item' => $item, 'badge' => $item]);
    }

    public function update(BadgeRequest $request, $id)
    {
        $item = BadgeRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['name'] = $data['title'] ?? $data['name'] ?? null;
                $data['key'] = $data['key'] ?? \Illuminate\Support\Str::slug($data['name'] ?? 'badge');
                unset($data['title'], $data['color']);
        if ($request->hasFile('icon')) {
            $data['image'] = UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->image ?? null);
        }

        BadgeRepository::updateByRequest($item, $data);

        return redirect()->route('admin.badges.index')->with('success', 'Badge updated successfully.');
    }

    public function destroy($id)
    {
        $item = BadgeRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
