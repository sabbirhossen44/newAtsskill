{{--
    Blog card (shared by homepage news feed and blog list).
    Expects a single $blog: slug, image, created_at, translation?->title,
    category?->translation?->title, author?->name.
--}}
@php
    $thumb = \App\Models\Media::resolveUrl($blog->image ?? null) ?? asset('images/placeholder.svg');
@endphp
<div class="blog__post-item h-100">
    <div class="blog__post-thumb">
        <a href="{{ route('blog.show', $blog->slug) }}"><img src="{{ $thumb }}" alt="{{ $blog->translation->title ?? '' }}" loading="lazy"></a>
        @if ($blog->category)
            <a href="{{ route('blogs', ['category' => $blog->category->slug]) }}" class="post-tag">{{ $blog->category->translation->title ?? $blog->category->slug }}</a>
        @endif
    </div>
    <div class="blog__post-content">
        <div class="blog__post-meta">
            <ul class="list-wrap">
                <li><i class="far fa-calendar-alt me-1"></i>{{ $blog->created_at?->format('d M, Y') }}</li>
                <li><i class="far fa-user me-1"></i>{{ __('by') }} {{ \Illuminate\Support\Str::limit($blog->author->name ?? 'ATS Skill', 14) }}</li>
            </ul>
        </div>
        <h4 class="title"><a href="{{ route('blog.show', $blog->slug) }}">{{ \Illuminate\Support\Str::limit($blog->translation->title ?? '', 50) }}</a></h4>
    </div>
</div>
