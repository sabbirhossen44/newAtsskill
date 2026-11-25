<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BlogCommentRequest;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogRepository;

class BlogController extends Controller
{
    function index()
    {
        $blogs = BlogRepository::paginateForIndex(request('search'), request('category'), (int) request('page', 1));
        $categories = BlogRepository::activeCategories();
        $popularBlogs = BlogRepository::popular();

        return view('frontend.pages.blog.index', compact('blogs', 'categories', 'popularBlogs'));
    }

    function show(string $slug)
    {
        $blog = BlogRepository::findBySlugWithActiveCategoryOrFail($slug);
        $latestBlogs = BlogRepository::latestExcept($blog);
        $categories = BlogRepository::activeCategories();

        // Comments are kept real-time (uncached) so a new comment shows at once.
        $comments = BlogCommentRepository::forBlog($blog);

        return view('frontend.pages.blog.show', compact('blog', 'latestBlogs', 'categories', 'comments'));
    }

    function submitComment(BlogCommentRequest $request)
    {
        BlogCommentRepository::storeByRequest([
            'blog_id' => $request->blog_id,
            'user_id' => auth('web')->user()->id,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->withFragment('comments')->with(['messege' => __('Comment added successfully. waiting for approval'), 'alert-type' => 'success']);
    }
}
