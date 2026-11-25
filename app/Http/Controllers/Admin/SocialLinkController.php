<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SocialLinkRequest;
use App\Repositories\SocialLinkRepository;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index(Request $request)
    {
        $socialLinks = SocialLinkRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.social-link.index', ['socialLinks' => $socialLinks, 'items' => $socialLinks]);
    }

    public function create()
    {
        return view('admin.social-link.create', []);
    }

    public function store(SocialLinkRequest $request)
    {
        $data = $request->validated();
        $data['link'] = $data['url'] ?? $data['link'] ?? null;
                unset($data['url'], $data['name']);

        SocialLinkRepository::storeByRequest($data);

        return redirect()->route('admin.social-link.index')->with('success', 'Social link created successfully.');
    }

    public function edit($id)
    {
        $item = SocialLinkRepository::findByIdOrFail($id);
        return view('admin.social-link.edit', ['item' => $item, 'socialLink' => $item]);
    }

    public function update(SocialLinkRequest $request, $id)
    {
        $item = SocialLinkRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['link'] = $data['url'] ?? $data['link'] ?? null;
                unset($data['url'], $data['name']);

        SocialLinkRepository::updateByRequest($item, $data);

        return redirect()->route('admin.social-link.index')->with('success', 'Social link updated successfully.');
    }

    public function destroy($id)
    {
        $item = SocialLinkRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.social-link.index')->with('success', 'Social link deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
