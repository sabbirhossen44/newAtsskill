{{--
    11. Expert Team Members. $teamMembers: team_members rows
    (name, designation, image, facebook, twitter, instagram, linkedin).
    Seeded data includes Azim Hossain — Founder & CEO.
--}}
<section class="team__area py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8">
                <div class="section__title text-center mb-5">
                    <h2 class="title">{{ __('Our Expert Team Members') }}</h2>
                </div>
            </div>
        </div>
        <div class="row justify-content-center g-4">
            @forelse ($teamMembers ?? [] as $member)
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <div class="team__item p-4 text-center">
                        <div class="team__thumb mx-auto" style="max-width:250px;">
                            <img src="{{ \App\Models\Media::resolveUrl($member->image ?? null) ?? asset('images/placeholder.svg') }}" alt="{{ $member->name ?? __('Team Member') }}" loading="lazy">
                        </div>
                        <div class="team__content mt-3">
                            <h4 class="title">{{ $member->name ?? __('Team Member') }}</h4>
                            <div class="designation fw-semibold">{{ $member->designation ?? __('Expert Member') }}</div>
                            <div class="team__social">
                                <ul class="list-wrap">
                                    @if (!empty($member->facebook))<li><a href="{{ $member->facebook }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>@endif
                                    @if (!empty($member->twitter))<li><a href="{{ $member->twitter }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>@endif
                                    @if (!empty($member->instagram))<li><a href="{{ $member->instagram }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>@endif
                                    @if (!empty($member->linkedin))<li><a href="{{ $member->linkedin }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>@endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><p class="text-center">{{ __('No Team Member Found!') }}</p></div>
            @endforelse
        </div>
    </div>
</section>
