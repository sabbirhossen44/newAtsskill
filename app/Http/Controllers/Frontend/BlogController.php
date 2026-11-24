<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;

class BlogController extends Controller
{
    function index() {
        $cacheDuration = 3600; // ১ ঘণ্টা ক্যাশ
        $search = request('search');
        $categorySlug = request('category');
        $page = request('page', 1);

        // যেহেতু সার্চ বা ক্যাটাগরি ফিল্টার এবং পেজিনেশন ডাইনামিক, তাই ইউনিক ক্যাশ কি (Cache Key) তৈরি করা হয়েছে
        $cacheKey = 'blog_index_' . md5($search . '_' . $categorySlug . '_page_' . $page);

        $blogs = Cache::remember($cacheKey, $cacheDuration, function () use ($search, $categorySlug) {
            $query = Blog::query();
            
            $query->when($search, function($query) use ($search) {
                $query->whereHas('translation', function($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

            $query->when($categorySlug, function($query) use ($categorySlug) {
                $query->whereHas('category', function($query) use ($categorySlug) {
                    $query->where('slug', $categorySlug);
                });
            });

            $query->whereHas('category', function($q) { $q->where('status', 1); });
            
            return $query->where(['status' => 1])->orderBy('created_at', 'desc')->paginate(9);
        });

        $categories = Cache::remember('blog_categories_all', $cacheDuration, function () {
            return BlogCategory::where('status', 1)->get();
        });

        $popularBlogs = Cache::remember('blog_popular_list', $cacheDuration, function () {
            return Blog::where(['status' => 1])
                ->whereHas('category', function($q) { $q->where('status', 1); })
                ->where('is_popular', 1)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        });

        return view('frontend.pages.blog.index', compact('blogs', 'categories', 'popularBlogs'));
    }

    function show(string $slug) {
        $cacheDuration = 3600;

        $blog = Cache::remember("blog_detail_{$slug}", $cacheDuration, function () use ($slug) {
            return Blog::where('slug', $slug)
                ->whereHas('category', function($q) { $q->where('status', 1); })
                ->firstOrFail();
        });

        $latestBlogs = Cache::remember("blog_latest_{$blog->id}", $cacheDuration, function () use ($blog) {
            return Blog::where(['status' => 1])
                ->where('id', '!=', $blog->id)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        });

        $categories = Cache::remember('blog_categories_all', $cacheDuration, function () {
            return BlogCategory::where('status', 1)->get();
        });

        // কমেন্টস রিয়েল-টাইম রাখার জন্য ক্যাশ করা হয়নি, কারণ ইউজার কমেন্ট করার সাথে সাথেই দেখতে চাইতে পারে
        $comments = BlogComment::where(['blog_id' => $blog->id])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.pages.blog.show', compact('blog', 'latestBlogs', 'categories', 'comments'));
    }

    function submitComment(Request $request) {
       $request->validate([
        'comment' => ['required', 'max:1000'], 
        'g-recaptcha-response' => setting('recaptcha_status') == 'active' ? 'required' : 'nullable',
       ], [
        'comment.required' => __('The comment field is required'),
        'comment.max' => __('The comment must not be greater than 1000 characters'),
        'g-recaptcha-response.required' => __('The reCAPTCHA verification is required'),
        'g-recaptcha-response.recaptcha' => __('The reCAPTCHA verification failed'),
       ]);

       $comment = new BlogComment();
       $comment->blog_id = $request->blog_id;
       $comment->user_id = auth('web')->user()->id;
       $comment->comment = $request->comment;
       $comment->save();

       return redirect()->back()->withFragment('comments')->with(['messege' => __('Comment added successfully. waiting for approval'), 'alert-type' => 'success']);
    }
}