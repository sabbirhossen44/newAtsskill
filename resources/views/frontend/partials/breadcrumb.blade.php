{{--
    Breadcrumb banner. Replaces the old <x-frontend.breadcrumb> component so
    pages need no component class. Usage:
    @include('frontend.partials.breadcrumb', ['title' => __('Courses'), 'links' => [['url' => route('home'), 'text' => __('Home')]]])
--}}
<section class="ats-breadcrumb">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="title mb-2">{{ $title ?? '' }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @foreach ($links ?? [['url' => route('home'), 'text' => __('Home')]] as $link)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" @if($loop->last) aria-current="page" @endif>
                                @if (! $loop->last && ! empty($link['url']))
                                    <a href="{{ $link['url'] }}">{{ $link['text'] }}</a>
                                @else
                                    {{ $link['text'] }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
