{{--
    Become an Instructor. $instructorRequestSetting (optional: need_certificate,
    need_identity_scan, bank_information) + $instructions (translated text).
    Posts to route('become-instructor.create'). Auth required (route middleware).
--}}
@extends('frontend.layouts.master')
@section('meta_title', __('Become an Instructor') . ' | ' . setting('app_name', 'ATS Skill'))

@section('contents')
    @include('frontend.partials.breadcrumb', ['title' => __('Become an Instructor'), 'links' => [['url' => route('home'), 'text' => __('Home')], ['url' => '', 'text' => __('Become an Instructor')]]])

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-3 p-md-4">
                        <h2 class="h4 fw-bold mb-3">{{ __('Apply to Become an Instructor') }}</h2>
                        @if ($instructions ?? null)<div class="alert alert-info">{!! $instructions !!}</div>@endif
                        @if ((enum_value($instructorRequest->status) ?? null) === 'pending')
                            <div class="alert alert-warning">{{ __('Your instructor request is pending review.') }}</div>
                        @elseif ((enum_value($instructorRequest->status) ?? null) === 'approved')
                            <div class="alert alert-success">{{ __('Your instructor request has been approved.') }} <a href="{{ route('instructor.dashboard') }}">{{ __('Go to Instructor Dashboard') }}</a></div>
                        @else
                            <form action="{{ route('become-instructor.create') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($instructorRequestSetting->need_certificate ?? true)
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Certificate') }}</label>
                                        <input type="file" name="certificate" class="form-control" accept="image/*,.pdf">
                                    </div>
                                @endif
                                @if ($instructorRequestSetting->need_identity_scan ?? true)
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Identity Scan (NID / Passport)') }}</label>
                                        <input type="file" name="identity_scan" class="form-control" accept="image/*,.pdf">
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Payout Account') }}</label>
                                    <input type="text" name="payout_account" class="form-control" placeholder="{{ __('e.g. bKash / Nagad / Bank account') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Payout Information') }}</label>
                                    <textarea name="payout_information" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Extra Information') }}</label>
                                    <textarea name="extra_information" rows="3" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ __('Submit Application') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
