<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Concerns\UploadsFiles;

class TeamMemberController extends Controller
{
    use UploadsFiles;
    public function index()
    {
        Paginator::useBootstrap();
        $teamMembers = TeamMember::orderBy('sort_order', 'asc')->paginate(15);
        return view('admin.team-members.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('admin.team-members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $teamMember = TeamMember::create($validated);

        if ($request->hasFile('image')) {
            $file_name = $this->uploadFile($request->image, 'uploads/team-members/', null);
            $teamMember->image = $file_name;
            $teamMember->save();
        }

        $notification = ['messege' => __('Created Successfully'), 'alert-type' => 'success'];
        return redirect()->route('admin.team-members.index')->with($notification);
    }

    public function edit($id)
    {
        $teamMember = TeamMember::findOrFail($id);
        return view('admin.team-members.edit', compact('teamMember'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable',
        ]);

        $teamMember = TeamMember::findOrFail($id);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $teamMember->update($validated);

        if ($request->hasFile('image')) {
            $old_image = $teamMember->image;
            $file_name = $this->uploadFile($request->image, 'uploads/team-members/', $old_image);
            $teamMember->image = $file_name;
            $teamMember->save();
        }

        $notification = ['messege' => __('Updated Successfully'), 'alert-type' => 'success'];
        return redirect()->route('admin.team-members.edit', $id)->with($notification);
    }

    public function destroy($id)
    {
        $teamMember = TeamMember::findOrFail($id);

        if ($teamMember->image && File::exists(public_path($teamMember->image))) {
            @unlink(public_path($teamMember->image));
        }

        $teamMember->delete();

        $notification = ['messege' => __('Deleted Successfully'), 'alert-type' => 'success'];
        return redirect()->route('admin.team-members.index')->with($notification);
    }

    public function statusUpdate($id)
    {
        $teamMember = TeamMember::findOrFail($id);
        $teamMember->status = $teamMember->status == 1 ? 0 : 1;
        $teamMember->save();

        return response()->json([
            'success' => true,
            'message' => __('Updated Successfully'),
        ]);
    }
}
