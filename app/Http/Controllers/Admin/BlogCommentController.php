<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RedirectTypeEnum;
use App\Http\Controllers\Concerns\ChecksAdminPermissions;
use App\Http\Controllers\Concerns\RedirectsWithMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCommentRequest;
use App\Repositories\BlogCommentRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogCommentController extends Controller
{
    use ChecksAdminPermissions;
    use RedirectsWithMessage;

    public function index(Request $request)
    {
        $this->checkAdminPermission('blog.comment.view');
        Paginator::useBootstrap();
        $comments = BlogCommentRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blog-comment.index', ['comments' => $comments, 'items' => $comments]);
    }

    public function show($id)
    {
        $this->checkAdminPermission('blog.comment.view');
        $item = BlogCommentRepository::findByIdOrFail($id);

        return view('admin.blog-comment.show', ['item' => $item, 'comment' => $item]);
    }

    public function update(BlogCommentRequest $request, $id)
    {
        $this->checkAdminPermission('blog.comment.view');
        $item = BlogCommentRepository::findByIdOrFail($id);
        $data = $request->validated();

        BlogCommentRepository::updateByRequest($item, $data);

        return $this->redirectWithMessage(RedirectTypeEnum::UPDATE->value, 'admin.blog-comment.index');
    }

    public function destroy($id)
    {
        $this->checkAdminPermission('blog.comment.view');
        $item = BlogCommentRepository::findByIdOrFail($id);

        BlogCommentRepository::delete($item);

        return $this->redirectWithMessage(RedirectTypeEnum::DELETE->value, 'admin.blog-comment.index');
    }

    public function statusUpdate(Request $request, $id)
    {
        $this->checkAdminPermission('blog.comment.view');
        $item = BlogCommentRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogCommentRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with(['messege' => __('Updated Successfully'), 'alert-type' => 'success']);
        }

        BlogCommentRepository::toggleStatus($item);

        return response()->json(['success' => true, 'message' => __('Updated Successfully')]);
    }
}
