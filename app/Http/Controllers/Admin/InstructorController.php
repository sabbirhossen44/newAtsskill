<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstructorRequest;
use App\Repositories\InstructorRepository;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $instructors = InstructorRepository::paginate($this->perPage($request, 10));

        return view('admin.instructors.index', compact('instructors'));
    }

    public function create()
    {
        return view('admin.instructors.create');
    }

    public function store(InstructorRequest $request)
    {
        InstructorRepository::storeByRequest($request->validated(), $request->file('image'));

        return redirect()->route('admin.instructors.index')->with('success', 'Instructor added successfully!');
    }

    public function show($id)
    {
        // No dedicated show view exists (old code had none either).
        return redirect()->route('admin.instructors.edit', $id);
    }

    public function edit($id)
    {
        // ভেরিয়েবল $instructors ব্যবহার করা হলো যাতে এডিট ব্লেড ফাইলে সব ভেরিয়েবল ঠিক থাকে
        $instructors = InstructorRepository::findByIdOrFail($id);

        return view('admin.instructors.edit', compact('instructors'));
    }

    public function update(InstructorRequest $request, $id)
    {
        $instructors = InstructorRepository::findByIdOrFail($id);

        InstructorRepository::updateByRequest($instructors, $request->validated(), $request->file('image'));

        return redirect()->route('admin.instructors.index')->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        $instructors = InstructorRepository::findByIdOrFail($id);

        InstructorRepository::delete($instructors);

        return redirect()->route('admin.instructors.index')->with('success', 'Deleted Successfully');
    }

    public function statusUpdate(Request $request, $id)
    {
        $instructors = InstructorRepository::findByIdOrFail($id);

        InstructorRepository::updateStatus($instructors, $request->input('status'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        }

        return back()->with('success', 'Status updated successfully!');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
