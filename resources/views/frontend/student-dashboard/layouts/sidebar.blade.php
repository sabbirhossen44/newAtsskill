{{-- Student dashboard sidebar — route names from old routes/web.php (student.* group). --}}
<div class="dashboard__sidebar-wrap">
    <div class="dashboard__sidebar-title mb-3">
        <h6 class="title mb-0">{{ __('Welcome') }}, {{ auth()->user()->name }}</h6>
    </div>
    <nav class="dashboard__sidebar-menu">
        <ul class="list-wrap">
            <li class="{{ Route::is('student.dashboard') ? 'active' : '' }}">
                <a href="{{ route('student.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>{{ __('Dashboard') }}</a>
            </li>
            <li class="{{ Route::is('student.orders.*') ? 'active' : '' }}">
                <a href="{{ route('student.orders.index') }}"><i class="fas fa-shopping-bag me-2"></i>{{ __('Order History') }}</a>
            </li>
            <li class="{{ Route::is('student.enrolled-courses') ? 'active' : '' }}">
                <a href="{{ route('student.enrolled-courses') }}"><i class="fas fa-graduation-cap me-2"></i>{{ __('Enrolled Courses') }}</a>
            </li>
            <li class="{{ Route::is('student.reviews.*') ? 'active' : '' }}">
                <a href="{{ route('student.reviews.index') }}"><i class="fas fa-star me-2"></i>{{ __('Reviews') }}</a>
            </li>
            <li class="{{ Route::is('student.quiz-attempts') ? 'active' : '' }}">
                <a href="{{ route('student.quiz-attempts') }}"><i class="fas fa-question-circle me-2"></i>{{ __('My Quiz Attempts') }}</a>
            </li>
        </ul>
    </nav>
    <div class="dashboard__sidebar-title mt-4 mb-3">
        <h6 class="title mb-0">{{ __('User') }}</h6>
    </div>
    <nav class="dashboard__sidebar-menu">
        <ul class="list-wrap">
            <li class="{{ Route::is('student.setting.*') ? 'active' : '' }}">
                <a href="{{ route('student.setting.index') }}"><i class="fas fa-user-cog me-2"></i>{{ __('Profile Settings') }}</a>
            </li>
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('student-logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}</a>
            </li>
        </ul>
    </nav>
</div>
<form id="student-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
