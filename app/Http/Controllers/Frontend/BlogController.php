<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BlogCommentRequest;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = BlogRepository::paginateForIndex(
            $request->input('search'),
            $request->input('category'),
            (int) $request->input('page', 1),
            $this->perPage($request)
        );
        $categories = BlogRepository::activeCategories();
        $popularBlogs = BlogRepository::popular();

        return view('frontend.pages.blog.index', compact('blogs', 'categories', 'popularBlogs'));
    }

    public function show(string $slug)
    {
        $blog = BlogRepository::findBySlugWithActiveCategoryOrFail($slug);
        $latestBlogs = BlogRepository::latestExcept($blog);
        $categories = BlogRepository::activeCategories();

        // Comments are kept real-time (uncached) so a new comment shows at once.
        $comments = BlogCommentRepository::forBlog($blog);

        return view('frontend.pages.blog.show', compact('blog', 'latestBlogs', 'categories', 'comments'));
    }

    public function submitComment(BlogCommentRequest $request)
    {
        BlogCommentRepository::storeByRequest([
            'blog_id' => $request->blog_id,
            'user_id' => auth('web')->user()->id,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->withFragment('comments')->with(['messege' => __('Comment added successfully. waiting for approval'), 'alert-type' => 'success']);
    }

    /**
     * Frontend listing page size. allSolution's admin perPage() whitelists
     * [10, 25, 50, 100] with default 15; the public blog listing has
     * always shown 9 per page, so that size is kept here.
     * Protected (not private) only because the base Controller still
     * defines protected perPage(); flip to private perPage() once the
     * base method is removed per spec section 1.
     */
    private function perPage(Request $request, int $default = 9): int
    {
        return $default;
    }
}
