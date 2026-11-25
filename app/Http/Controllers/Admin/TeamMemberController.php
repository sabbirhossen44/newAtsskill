<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Repositories\TeamMemberRepository;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index(Request $request)
    {
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

        return redirect()->route('admin.team-members.index')->with('success', __('Created Successfully'));
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

        return redirect()->route('admin.team-members.edit', $id)->with('success', __('Updated Successfully'));
    }

    public function destroy($id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        TeamMemberRepository::deleteRecord($teamMember);

        return redirect()->route('admin.team-members.index')->with('success', __('Deleted Successfully'));
    }

    public function statusUpdate(Request $request, $id)
    {
        $teamMember = TeamMemberRepository::findByIdOrFail($id);

        TeamMemberRepository::toggleStatus($teamMember);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
        }

        return back()->with('success', __('Updated Successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
