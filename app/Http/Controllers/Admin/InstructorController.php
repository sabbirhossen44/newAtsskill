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

        $notification = ['messege' => 'Instructor added successfully!', 'alert-type' => 'success'];

        return redirect()->route('admin.instructors.index')->with($notification);
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

        $notification = ['messege' => 'Updated Successfully', 'alert-type' => 'success'];

        return redirect()->route('admin.instructors.index')->with($notification);
    }

    public function destroy($id)
    {
        $instructors = InstructorRepository::findByIdOrFail($id);

        InstructorRepository::delete($instructors);

        $notification = ['messege' => 'Deleted Successfully', 'alert-type' => 'success'];

        return redirect()->route('admin.instructors.index')->with($notification);
    }

    public function statusUpdate(Request $request, $id)
    {
        $instructors = InstructorRepository::findByIdOrFail($id);

        InstructorRepository::updateStatus($instructors, $request->input('status'));

        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }
}
