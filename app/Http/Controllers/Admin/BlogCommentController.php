<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCommentRequest;
use App\Repositories\BlogCommentRepository;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = BlogCommentRepository::paginateAdmin($request->input('keyword') ?? $request->input('search'), $this->perPage($request));
        return view('admin.blog-comment.index', ['comments' => $comments, 'items' => $comments]);
    }

    public function show($id)
    {
        $item = BlogCommentRepository::findByIdOrFail($id);

        return view('admin.blog-comment.show', ['item' => $item, 'comment' => $item]);
    }

    public function update(BlogCommentRequest $request, $id)
    {
        $item = BlogCommentRepository::findByIdOrFail($id);
        $data = $request->validated();

        BlogCommentRepository::updateByRequest($item, $data);

        return redirect()->route('admin.blog-comment.index')->with('success', 'Blog comment updated successfully.');
    }

    public function destroy($id)
    {
        $item = BlogCommentRepository::findByIdOrFail($id);

        $item->delete();

        return redirect()->route('admin.blog-comment.index')->with('success', 'Blog comment deleted successfully.');
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = BlogCommentRepository::findByIdOrFail($id);

        if ($request->filled('status')) {
            BlogCommentRepository::updateStatus($item, $request->input('status'));

            return redirect()->back()->with('success', __('Updated Successfully'));
        }

        BlogCommentRepository::toggleStatus($item);

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
