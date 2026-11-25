<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CounterSectionRequest;
use App\Repositories\CounterSectionRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CounterSectionController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('section.management');
        Paginator::useBootstrap();
        $sections = CounterSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.counter-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        $this->checkAdminPermission('section.management');
        return view('admin.counter-section.create', []);
    }

    public function store(CounterSectionRequest $request)
    {
        $this->checkAdminPermission('section.management');
        $data = $request->validated();

        CounterSectionRepository::storeByRequest($data);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'admin.counter-section.index');
    }

    public function edit($id)
    {
        $this->checkAdminPermission('section.management');
        $item = CounterSectionRepository::findByIdOrFail($id);
        return view('admin.counter-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(CounterSectionRequest $request, $id)
    {
        $this->checkAdminPermission('section.management');
        $item = CounterSectionRepository::findByIdOrFail($id);
        $data = $request->validated();

        CounterSectionRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.counter-section.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('section.management');
        $item = CounterSectionRepository::findByIdOrFail($id);

        CounterSectionRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.counter-section.index');
    }
}
