<?php

namespace App\Repositories;

use App\Models\BlogComment;
use App\Models\Blog;
use Illuminate\Support\Collection;

class BlogCommentRepository
{
    public static function model()
    {
        return BlogComment::class;
    }

    public static function forBlog(Blog $blog): Collection
    {
        return static::model()::query()
            ->where(['blog_id' => $blog->id])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function storeByRequest(array $data): BlogComment
    {
        return static::model()::query()->create([
            'blog_id' => $data['blog_id'],
            'user_id' => $data['user_id'],
            'comment' => $data['comment'],
        ]);
    }
}
