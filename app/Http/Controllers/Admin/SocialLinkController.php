<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SocialLinkRequest;
use App\Repositories\SocialLinkRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class SocialLinkController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('social.link.management');
        Paginator::useBootstrap();
        $socialLinks = SocialLinkRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.social-link.index', ['socialLinks' => $socialLinks, 'items' => $socialLinks]);
    }

    public function create()
    {
        $this->checkAdminPermission('social.link.management');
        return view('admin.social-link.create', []);
    }

    public function store(SocialLinkRequest $request)
    {
        $this->checkAdminPermission('social.link.management');
        $data = $request->validated();
        $data['link'] = $data['url'] ?? $data['link'] ?? null;
                unset($data['url'], $data['name']);

        SocialLinkRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.social-link.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('social.link.management');
        $item = SocialLinkRepository::findByIdOrFail($id);
        return view('admin.social-link.edit', ['item' => $item, 'socialLink' => $item]);
    }

    public function update(SocialLinkRequest $request, $id)
    {
        $this->checkAdminPermission('social.link.management');
        $item = SocialLinkRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['link'] = $data['url'] ?? $data['link'] ?? null;
                unset($data['url'], $data['name']);

        SocialLinkRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.social-link.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('social.link.management');
        $item = SocialLinkRepository::findByIdOrFail($id);

        SocialLinkRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.social-link.index');
    }
}
