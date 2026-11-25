<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseReviewRequest;
use App\Repositories\CourseReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CourseReviewController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('course.management');
        Paginator::useBootstrap();
        $reviews = CourseReviewRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.course-review.index', ['reviews' => $reviews, 'items' => $reviews]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseReviewRepository::findByIdOrFail($id);

        return view('admin.course-review.show', ['item' => $item, 'review' => $item]);
    }

    public function edit($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseReviewRepository::findByIdOrFail($id);
        return view('admin.course-review.edit', ['item' => $item, 'review' => $item]);
    }

    public function update(CourseReviewRequest $request, $id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseReviewRepository::findByIdOrFail($id);
        $data = $request->validated();
        $data['review'] = $data['comment'] ?? $data['review'] ?? null;
                unset($data['comment']);

        CourseReviewRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.course-review.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('course.management');
        $item = CourseReviewRepository::findByIdOrFail($id);

        CourseReviewRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.course-review.index');
    }
}
