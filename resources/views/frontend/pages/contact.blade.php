{{--
    Contact. $contact: contact_sections row (address, phone_one, phone_two,
    email_one, email_two, map — raw embed HTML). Form posts to
    route('contact.send-mail').
--}}
@extends('frontend.layouts.master')
@section('meta_title', $seo_setting['contact_page']['seo_title'] ?? __('Contact Us') . ' | ' . setting('app_name', 'ATS Skill'))
@section('meta_description', $seo_setting['contact_page']['seo_description'] ?? '')

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Contact Us'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Contact Us')]]])

    <section class="contact-area py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="contact-info-wrap card p-3 h-100">
                        <ul class="list-unstyled mb-0">
                            @if ($contact->address ?? null)
                                <li class="mb-3"><h4 class="h6 fw-bold"><i class="fas fa-map-marker-alt me-2 text-primary"></i>{{ __('Address') }}</h4><p class="mb-0">{{ $contact->address }}</p></li>
                            @endif
                            @if (($contact->phone_one ?? null) || ($contact->phone_two ?? null))
                                <li class="mb-3"><h4 class="h6 fw-bold"><i class="fas fa-phone-alt me-2 text-primary"></i>{{ __('Phone') }}</h4>
                                    @if ($contact->phone_one)<a href="tel:{{ $contact->phone_one }}" class="d-block">{{ $contact->phone_one }}</a>@endif
                                    @if ($contact->phone_two)<a href="tel:{{ $contact->phone_two }}" class="d-block">{{ $contact->phone_two }}</a>@endif
                                </li>
                            @endif
                            @if (($contact->email_one ?? null) || ($contact->email_two ?? null))
                                <li class="mb-3"><h4 class="h6 fw-bold"><i class="fas fa-envelope me-2 text-primary"></i>{{ __('Email') }}</h4>
                                    @if ($contact->email_one)<a href="mailto:{{ $contact->email_one }}" class="d-block">{{ $contact->email_one }}</a>@endif
                                    @if ($contact->email_two)<a href="mailto:{{ $contact->email_two }}" class="d-block">{{ $contact->email_two }}</a>@endif
                                </li>
                            @endif
                        </ul>
                        <div class="mt-3">
                            @if ($contact->map ?? null)
                                {!! $contact->map !!}
                            @else
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.4458875480154!2d90.53312837620707!3d23.552424196346195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755ade89853b8bf%3A0x4194104552725921!2s1500%20Jubilee%20Rd%2C%20Munshiganj%2C%20Bangladesh!5e0!3m2!1sen!2sus!4v1792762476627!5m2!1sen!2sus" width="100%" height="220" style="border:0;" loading="lazy" title="{{ __('Map') }}"></iframe>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card p-3 p-md-4">
                        <h2 class="h4 fw-bold mb-3">{{ __('Send Us a Message') }}</h2>
                        <form action="{{ route('contact.send-mail') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">{{ __('Name') }}</label><input type="text" name="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email ?? '' }}" required></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Phone') }}</label><input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone ?? '' }}"></div>
                                <div class="col-md-6"><label class="form-label">{{ __('Subject') }}</label><input type="text" name="subject" class="form-control"></div>
                                <div class="col-12"><label class="form-label">{{ __('Message') }}</label><textarea name="message" rows="5" class="form-control" required></textarea></div>
                                <div class="col-12"><button type="submit" class="btn btn-primary">{{ __('Send Message') }}</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
