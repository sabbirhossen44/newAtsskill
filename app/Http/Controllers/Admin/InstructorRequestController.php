<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstructorRequestStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Enums\RedirectTypeEnum;
use App\Http\Requests\Admin\InstructorRequestUpdateRequest;
use App\Models\User;
use App\Repositories\InstructorRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Instructor requests. Updating a request to `approved` also makes
 * the user an instructor, as the old controller did. (The old
 * approval/rejection emails are not sent: the old EmailService /
 * jobs do not exist in this rebuild.)
 */
class InstructorRequestController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('instructor.request.list');
        Paginator::useBootstrap();
        $query = \App\Models\InstructorRequest::query()->with(['user']);
        $query->when($request->filled('keyword'), function ($q) use ($request) {
            $q->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->keyword.'%')
                    ->orWhere('email', 'like', '%'.$request->keyword.'%');
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $requests = $query->orderBy('id', 'desc')->paginate($this->perPage($request));

        return view('admin.instructor-request.index', ['requests' => $requests, 'instructorRequests' => $requests, 'items' => $requests]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('instructor.request.list');
        $item = \App\Models\InstructorRequest::with(['user'])->findOrFail($id);

        return view('admin.instructor-request.show', ['item' => $item, 'instructorRequest' => $item]);
    }

    public function edit($id)
    {
        $this->checkAdminPermission('instructor.request.list');
        $item = \App\Models\InstructorRequest::findOrFail($id);
        $user = User::find($item->user_id);

        return view('admin.instructor-request.edit', ['item' => $item, 'instructorRequest' => $item, 'user' => $user]);
    }

    public function update(InstructorRequestUpdateRequest $request, $id)
    {
        $this->checkAdminPermission('instructor.request.list');
        $instructorRequest = \App\Models\InstructorRequest::findOrFail($id);
        $status = InstructorRequestStatusEnum::tryFrom($request->input('status')) ?? $instructorRequest->status;
        $instructorRequest->update(['status' => $status]);

        if ($status === InstructorRequestStatusEnum::APPROVED) {
            User::where('id', $instructorRequest->user_id)->update(['role' => UserRoleEnum::INSTRUCTOR->value]);
        }

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.instructor-request.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('instructor.request.list');
        \App\Models\InstructorRequest::findOrFail($id)->delete();

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.instructor-request.index');
    }
}
