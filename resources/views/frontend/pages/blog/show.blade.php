{{--
    Blog details. $blog (translation title/description, image, category,
    author, created_at, views), $relatedBlogs, $blogComments.
    Comment form posts to route('blog.submit-comment').
--}}
@extends('frontend.layouts.master')
@section('meta_title', ($blog->translation->title ?? __('Blog')) . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Blog Details'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => route('blogs'), 'text' => __('Blog')], ['url' => '', 'text' => \Illuminate\Support\Str::limit($blog->translation->title ?? '', 40)]]])

    <section class="blog__details-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-xl-9 col-lg-8">
                    <article class="card p-3 p-md-4">
                        <img src="{{ \App\Models\Media::resolveUrl($blog->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $blog->translation->title ?? '' }}" class="img-fluid rounded mb-3">
                        <div class="blog__post-meta">
                            <ul class="list-inline text-muted">
                                <li class="list-inline-item"><i class="far fa-calendar-alt me-1"></i>{{ $blog->created_at?->format('d M, Y') }}</li>
                                <li class="list-inline-item"><i class="far fa-user me-1"></i>{{ __('by') }} {{ $blog->author->name ?? 'ATS Skill' }}</li>
                                <li class="list-inline-item"><i class="far fa-eye me-1"></i>{{ $blog->views ?? 0 }} {{ __('Views') }}</li>
                                @if ($blog->category)<li class="list-inline-item"><i class="far fa-folder me-1"></i>{{ $blog->category->translation->title ?? '' }}</li>@endif
                            </ul>
                        </div>
                        <h1 class="h2 fw-bold">{{ $blog->translation->title ?? '' }}</h1>
                        <div class="mt-3">{!! $blog->translation->description ?? '' !!}</div>
                        @if ($blog->tags ?? null)
                            <div class="mt-3">
                                @foreach (explode(',', $blog->tags) as $tag)
                                    <span class="badge bg-light text-dark border me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <div class="card p-3 p-md-4 mt-4">
                        <h3 class="h5 fw-bold mb-3">{{ __('Comments') }} ({{ count($blogComments ?? []) }})</h3>
                        @forelse ($blogComments ?? [] as $comment)
                            <div class="border-bottom py-2">
                                <strong>{{ $comment->name ?? $comment->user->name ?? __('Guest') }}</strong>
                                <small class="text-muted ms-2">{{ $comment->created_at?->format('d M, Y') }}</small>
                                <p class="mb-0">{{ $comment->comment }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('No comments yet. Be the first to comment!') }}</p>
                        @endforelse
                        <form action="{{ route('blog.submit-comment') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="blog_id" value="{{ $blog->id }}">
                            <div class="row g-3">
                                <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ auth()->user()->name ?? '' }}" required></div>
                                <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="{{ __('Email') }}" value="{{ auth()->user()->email ?? '' }}"></div>
                                <div class="col-md-4"><input type="text" name="phone" class="form-control" placeholder="{{ __('Phone') }}"></div>
                                <div class="col-12"><textarea name="comment" rows="4" class="form-control" placeholder="{{ __('Write your comment...') }}" required></textarea></div>
                                <div class="col-12"><button type="submit" class="btn btn-primary">{{ __('Submit Comment') }}</button></div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <aside class="card p-3">
                        <h4 class="h6 fw-bold">{{ __('Related Posts') }}</h4>
                        @foreach ($relatedBlogs ?? [] as $related)
                            <div class="d-flex gap-2 mb-3">
                                <img src="{{ \App\Models\Media::resolveUrl($related->image ?? null) ?? asset('images/placeholder.svg') }}" alt="" style="width:72px;height:56px;object-fit:cover;" class="rounded">
                                <a href="{{ route('blog.show', $related->slug) }}" class="fw-semibold small">{{ \Illuminate\Support\Str::limit($related->translation->title ?? '', 50) }}</a>
                            </div>
                        @endforeach
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection
