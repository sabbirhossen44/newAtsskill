<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CounterSectionRequest;
use App\Repositories\CounterSectionRepository;
use Illuminate\Http\Request;

class CounterSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = CounterSectionRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.counter-section.index', ['sections' => $sections, 'items' => $sections]);
    }

    public function create()
    {
        return view('admin.counter-section.create', []);
    }

    public function store(CounterSectionRequest $request)
    {
        $data = $request->validated();

        CounterSectionRepository::storeByRequest($data);

        return redirect()->route('admin.counter-section.index')->with('success', 'Counter section created successfully.');
    }

    public function edit($id)
    {
        $item = CounterSectionRepository::findByIdOrFail($id);
        return view('admin.counter-section.edit', ['item' => $item, 'section' => $item]);
    }

    public function update(CounterSectionRequest $request, $id)
    {
        $item = CounterSectionRepository::findByIdOrFail($id);
        $data = $request->validated();

        CounterSectionRepository::updateByRequest($item, $data);

        return redirect()->route('admin.counter-section.index')->with('success', 'Counter section updated successfully.');
    }

    public function destroy($id)
    {
        $item = CounterSectionRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.counter-section.index')->with('success', 'Counter section deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
