<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instructor;
use File;
use Illuminate\Support\Facades\Session;

class InstructorController extends Controller
{
    public function index()
    {
        $instructors = Instructor::paginate(10);
        return view('admin.instructors.index', compact('instructors'));
    }

    public function create()
    {
        return view('admin.instructors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'designation' => 'required|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imageName = null;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = 'instructor_'.time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/instructors/'), $imageName);
        }
    
        Instructor::create([
            'name'        => $request->name,
            'designation' => $request->designation,
            'image'       => 'uploads/instructors/'.$imageName,
            'facebook'    => $request->facebook,
            'twitter'     => $request->twitter,
            'youtube'     => $request->youtube,
            'linkedin'    => $request->linkedin,
            'instagram'   => $request->instagram,
            'sort_order'  => $request->sort_order ?? 0,
            'status'      => $request->status == '1' ? 1 : 0,
        ]);

        $notification = ['messege' => 'Instructor added successfully!', 'alert-type' => 'success'];
        return redirect()->route('admin.instructors.index')->with($notification);
    }
    
    public function edit($id)
    {
       // ভেরিয়েবল $instructors ব্যবহার করা হলো যাতে এডিট ব্লেড ফাইলে সব ভেরিয়েবল ঠিক থাকে
       $instructors = Instructor::findOrFail($id);
       return view('admin.instructors.edit', compact('instructors'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $instructors = Instructor::findOrFail($id);
        $instructors->name = $request->name;
        $instructors->designation = $request->designation;
        $instructors->facebook = $request->facebook;
        $instructors->twitter = $request->twitter;
        $instructors->youtube = $request->youtube;
        $instructors->linkedin = $request->linkedin;
        $instructors->instagram = $request->instagram;
        $instructors->sort_order = $request->sort_order ?? 0;
        $instructors->status = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            if ($instructors->image && File::exists(public_path($instructors->image))) {
                unlink(public_path($instructors->image));
            }
            $image = $request->file('image');
            $imageName = 'instructor_'.time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/instructors/'), $imageName);
            $instructors->image = 'uploads/instructors/'.$imageName;
        }
        $instructors->save();

        $notification = ['messege' => 'Updated Successfully', 'alert-type' => 'success'];
        return redirect()->route('admin.instructors.index')->with($notification);
    }
    
    public function destroy($id)
    {
        $instructors = Instructor::findOrFail($id);
        if ($instructors->image && File::exists(public_path($instructors->image))) {
            unlink(public_path($instructors->image));
        }
        $instructors->delete();

        $notification = ['messege' => 'Deleted Successfully', 'alert-type' => 'success'];
        return redirect()->route('admin.instructors.index')->with($notification);
    }

    public function statusUpdate(Request $request, $id) 
    {
        $instructors = Instructor::findOrFail($id);
        $instructors->status = $request->status;
        $instructors->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }
}