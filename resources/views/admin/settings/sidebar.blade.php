<div class="list-group settings-nav">
    <a href="{{ route('admin.settings') }}" class="list-group-item list-group-item-action {{ Route::is('admin.settings') ? 'active' : '' }}">{{ __('General Settings') }}</a>
    <a href="{{ route('admin.general-setting') }}" class="list-group-item list-group-item-action {{ Route::is('admin.general-setting') ? 'active' : '' }}">{{ __('General') }}</a>
    <a href="{{ route('admin.commission-setting') }}" class="list-group-item list-group-item-action {{ Route::is('admin.commission-setting') ? 'active' : '' }}">{{ __('Commission') }}</a>
    <a href="{{ route('admin.seo-setting') }}" class="list-group-item list-group-item-action {{ Route::is('admin.seo-setting') ? 'active' : '' }}">{{ __('SEO') }}</a>
    <a href="{{ route('admin.email-configuration') }}" class="list-group-item list-group-item-action {{ Route::is('admin.email-configuration') ? 'active' : '' }}">{{ __('Email Configuration') }}</a>
    <a href="{{ route('admin.custom-code') }}" class="list-group-item list-group-item-action {{ Route::is('admin.custom-code') ? 'active' : '' }}">{{ __('Custom Code') }}</a>
    <a href="{{ route('admin.marketing-setting') }}" class="list-group-item list-group-item-action {{ Route::is('admin.marketing-setting') ? 'active' : '' }}">{{ __('Marketing') }}</a>
    <a href="{{ route('admin.crediential-setting') }}" class="list-group-item list-group-item-action {{ Route::is('admin.crediential-setting') ? 'active' : '' }}">{{ __('Credentials') }}</a>
    <a href="{{ route('admin.basicpayment') }}" class="list-group-item list-group-item-action">{{ __('Basic Payments') }}</a>
    <a href="{{ route('admin.paymentgateway') }}" class="list-group-item list-group-item-action">{{ __('Payment Gateways') }}</a>
</div>
