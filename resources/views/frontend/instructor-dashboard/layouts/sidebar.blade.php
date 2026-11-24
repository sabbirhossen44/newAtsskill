{{-- Instructor dashboard sidebar — route names from old routes/web.php (instructor.* group). --}}
<div class="dashboard__sidebar-wrap">
    <div class="dashboard__sidebar-title mb-3">
        <h6 class="title mb-0">{{ __('Welcome') }}, {{ auth()->user()->name }}</h6>
    </div>
    <nav class="dashboard__sidebar-menu">
        <ul class="list-wrap">
            <li class="{{ Route::is('instructor.dashboard') ? 'active' : '' }}">
                <a href="{{ route('instructor.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>{{ __('Dashboard') }}</a>
            </li>
            <li class="{{ Route::is('instructor.courses.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.courses.index') }}"><i class="fas fa-book me-2"></i>{{ __('Courses') }}</a>
            </li>
            <li class="{{ Route::is('instructor.lesson-questions.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.lesson-questions.index') }}"><i class="fas fa-question-circle me-2"></i>{{ __('Lesson Questions') }}</a>
            </li>
            <li class="{{ Route::is('instructor.payout.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.payout.index') }}"><i class="fas fa-wallet me-2"></i>{{ __('Request Payout') }}</a>
            </li>
            <li class="{{ Route::is('instructor.announcements.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.announcements.index') }}"><i class="fas fa-bullhorn me-2"></i>{{ __('Announcement') }}</a>
            </li>
            <li class="{{ Route::is('instructor.my-sells.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.my-sells.index') }}"><i class="fas fa-chart-line me-2"></i>{{ __('My Sales') }}</a>
            </li>
        </ul>
    </nav>
    <div class="dashboard__sidebar-title mt-4 mb-3">
        <h6 class="title mb-0">{{ __('User') }}</h6>
    </div>
    <nav class="dashboard__sidebar-menu">
        <ul class="list-wrap">
            <li class="{{ Route::is('instructor.zoom-setting.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.zoom-setting.index') }}"><i class="fas fa-video me-2"></i>{{ __('Zoom live setting') }}</a>
            </li>
            <li class="{{ Route::is('instructor.jitsi-setting.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.jitsi-setting.index') }}"><i class="fas fa-video me-2"></i>{{ __('Jitsi live setting') }}</a>
            </li>
            <li class="{{ Route::is('instructor.setting.*') ? 'active' : '' }}">
                <a href="{{ route('instructor.setting.index') }}"><i class="fas fa-user-cog me-2"></i>{{ __('Profile Settings') }}</a>
            </li>
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('instructor-logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}</a>
            </li>
        </ul>
    </nav>
</div>
<form id="instructor-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
