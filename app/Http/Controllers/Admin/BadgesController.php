<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BadgeRequest;
use App\Repositories\BadgeRepository;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BadgesController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('badge.management');
        Paginator::useBootstrap();
        $badges = BadgeRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.badges.index', ['badges' => $badges, 'items' => $badges]);
    }

    public function create()
    {
        $this->checkAdminPermission('badge.management');
        return view('admin.badges.create', []);
    }

    public function store(BadgeRequest $request)
    {
        $this->checkAdminPermission('badge.management');
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

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.badges.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('badge.management');
        $item = BadgeRepository::findByIdOrFail($id);
        return view('admin.badges.edit', ['item' => $item, 'badge' => $item]);
    }

    public function update(BadgeRequest $request, $id)
    {
        $this->checkAdminPermission('badge.management');
        $item = BadgeRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['name'] = $data['title'] ?? $data['name'] ?? null;
                $data['key'] = $data['key'] ?? \Illuminate\Support\Str::slug($data['name'] ?? 'badge');
                unset($data['title'], $data['color']);
        if ($request->hasFile('icon')) {
            $data['image'] = UploadService::store($request->file('icon'), 'uploads/custom-images/', $item->image ?? null);
        }

        BadgeRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.badges.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('badge.management');
        $item = BadgeRepository::findByIdOrFail($id);

        BadgeRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.badges.index');
    }
}
