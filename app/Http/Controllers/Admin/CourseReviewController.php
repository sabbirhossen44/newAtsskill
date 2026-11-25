<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseReviewRequest;
use App\Repositories\CourseReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseReviewController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $reviews = CourseReviewRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-review.index', ['reviews' => $reviews, 'items' => $reviews]);
    }

    public function show($id)
    {
        $item = CourseReviewRepository::findByIdOrFail($id);

        return view('admin.course-review.show', ['item' => $item, 'review' => $item]);
    }

    public function edit($id)
    {
        $item = CourseReviewRepository::findByIdOrFail($id);
        return view('admin.course-review.edit', ['item' => $item, 'review' => $item]);
    }

    public function update(CourseReviewRequest $request, $id)
    {
        $item = CourseReviewRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['review'] = $data['comment'] ?? $data['review'] ?? null;
                unset($data['comment']);

        CourseReviewRepository::updateByRequest($item, $data);

        return redirect()->route('admin.course-review.index')->with('success', 'Course review updated successfully.');
    }

    public function destroy($id)
    {
        $item = CourseReviewRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.course-review.index')->with('success', 'Course review deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
