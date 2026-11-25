<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Models\Course;
use App\Repositories\CourseDeleteRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Course delete requests. Approving (`action=inactive`) soft-deletes
 * the course and marks the request handled; anything else restores
 * the course and re-opens the request, as the old controller did.
 */
class CourseDeleteRequestController extends Controller
{
    use ChecksAdminPermissions;

    public function index()
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $deleteRequests = CourseDeleteRequestRepository::paginateAdmin(null, $this->perPage(request()));
        $messages = $deleteRequests;

        return view('admin.course-delete-request.index', ['deleteRequests' => $deleteRequests, 'messages' => $messages, 'items' => $deleteRequests]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseDeleteRequestRepository::findByIdOrFail((int) $id);

        return view('admin.course-delete-request.show', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $message = CourseDeleteRequestRepository::findByIdOrFail((int) $id);
        if ($request->input('action') === 'inactive') {
            Course::findOrFail($message->course_id)->delete();
            $message->update(['status' => 1]);
        } else {
            Course::withTrashed()->findOrFail($message->course_id)->restore();
            $message->update(['status' => 0]);
        }

        return redirect()->back()->with(['messege' => __('Updated successfully'), 'alert-type' => 'success']);
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $message = CourseDeleteRequestRepository::findByIdOrFail((int) $id);
        CourseDeleteRequestRepository::delete($message);

        return redirect()->route('admin.course-delete-request.index')->with(['messege' => __('Deleted successfully'), 'alert-type' => 'success']);
    }
}
