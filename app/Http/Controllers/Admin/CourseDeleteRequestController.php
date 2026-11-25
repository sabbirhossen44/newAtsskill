<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Repositories\CourseDeleteRequestRepository;
use Illuminate\Http\Request;

/**
 * Course delete requests. Approving (`action=inactive`) soft-deletes
 * the course and marks the request handled; anything else restores
 * the course and re-opens the request, as the old controller did.
 */
class CourseDeleteRequestController extends Controller
{
    public function index()
    {
        $deleteRequests = CourseDeleteRequestRepository::paginateAdmin(null, $this->perPage(request()));
        $messages = $deleteRequests;

        return view('admin.course-delete-request.index', ['deleteRequests' => $deleteRequests, 'messages' => $messages, 'items' => $deleteRequests]);
    }

    public function show($id)
    {
        $item = CourseDeleteRequestRepository::findByIdOrFail((int) $id);

        return view('admin.course-delete-request.show', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $message = CourseDeleteRequestRepository::findByIdOrFail((int) $id);
        if ($request->input('action') === 'inactive') {
            Course::findOrFail($message->course_id)->delete();
            $message->update(['status' => 1]);
        } else {
            Course::withTrashed()->findOrFail($message->course_id)->restore();
            $message->update(['status' => 0]);
        }

        return redirect()->back()->with('success', __('Updated successfully'));
    }

    public function destroy($id)
    {
        $message = CourseDeleteRequestRepository::findByIdOrFail((int) $id);
        $message->delete();

        return redirect()->route('admin.course-delete-request.index')->with('success', __('Deleted successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
