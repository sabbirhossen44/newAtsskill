<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Repositories\TeamMemberRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class TeamMemberController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $teamMembers = TeamMemberRepository::paginate($this->perPage($request));

        return view('admin.team-members.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('admin.team-members.create');
    }

    public function store(TeamMemberRequest $request)
    {
        TeamMemberRepository::storeByRequest($request->validated(), $request->file('image'));

        $notification = ['messege' => __('Created Successfully'), 'alert-type' => 'success'];

        return redirect()->route('admin.team-members.index')->with($notification);
    }

    public function show($id)
    {
        // No dedicated show view exists (old code had none either).
        return redirect()->route('admin.team-members.edit', $id);
    }

    public function edit($id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        return view('admin.team-members.edit', compact('teamMember') + ['item' => $teamMember]);
    }

    public function update(TeamMemberRequest $request, $id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        TeamMemberRepository::updateByRequest($teamMember, $request->validated(), $request->file('image'));

        $notification = ['messege' => __('Updated Successfully'), 'alert-type' => 'success'];

        return redirect()->route('admin.team-members.edit', $id)->with($notification);
    }

    public function destroy($id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        TeamMemberRepository::delete($teamMember);

        $notification = ['messege' => __('Deleted Successfully'), 'alert-type' => 'success'];

        return redirect()->route('admin.team-members.index')->with($notification);
    }

    public function statusUpdate($id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        TeamMemberRepository::toggleStatus($teamMember);

        return response()->json([
            'success' => true,
            'message' => __('Updated Successfully'),
        ]);
    }
}
