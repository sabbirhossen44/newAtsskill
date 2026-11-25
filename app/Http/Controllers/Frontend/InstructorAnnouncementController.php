<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Requests\Frontend\AnnouncementRequest;
use App\Repositories\AnnouncementRepository;

class InstructorAnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use RedirectsWithMessage;

    function index()
    {
        $announcements = AnnouncementRepository::allForInstructor(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.announcement.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = AnnouncementRepository::coursesForInstructor(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.announcement.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    function store(AnnouncementRequest $request)
    {
        AnnouncementRepository::storeByRequest([
            'instructor_id' => auth('web')->user()->id,
            'course_id' => $request->course,
            'title' => $request->title,
            'announcement' => $request->announcement,
        ]);

        return $this->redirectWithMessage(RedirectTypeEnum::CREATE->value, 'instructor.announcements.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $announcement = AnnouncementRepository::findByIdOrFail($id);
        $courses = AnnouncementRepository::coursesForInstructor(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.announcement.edit', compact('courses', 'announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AnnouncementRequest $request, string $id)
    {
        $announcement = AnnouncementRepository::findByIdOrFail($id);
        AnnouncementRepository::updateByRequest($announcement, [
            'course_id' => $request->course,
            'title' => $request->title,
            'announcement' => $request->announcement,
        ]);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'instructor.announcements.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $announcement = AnnouncementRepository::findForInstructorOrFail($id, auth('web')->user()->id);
        AnnouncementRepository::delete($announcement);

        return response()->json(['status' => 'success', 'message' => 'Announcement deleted successfully']);
    }
}
