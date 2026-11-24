@extends('admin.master_layout')
@section('title')
    <title>Coupons — Create | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Coupons — Create</h1>
        <a href="{{ route('admin.coupon.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.coupon.store') }}" method="POST">
            @csrf
                        <div class="row g-3">
<div class="col-md-6">
<label class="form-label" for="code">Code</label>
<input type="text" name="code" id="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" required>
@error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="type">Type</label>
<select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
<option value="percentage" {{ (old('type')) == 'percentage' ? 'selected' : '' }}>Percentage</option>
<option value="fixed" {{ (old('type')) == 'fixed' ? 'selected' : '' }}>Fixed</option>
</select>
@error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="value">Value</label>
<input type="number" step="any" name="value" id="value" value="{{ old('value') }}" class="form-control @error('value') is-invalid @enderror">
@error('value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="discount">Discount</label>
<input type="number" step="any" name="discount" id="discount" value="{{ old('discount') }}" class="form-control @error('discount') is-invalid @enderror">
@error('discount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="expired_date">Expiry Date</label>
<input type="date" name="expired_date" id="expired_date" value="{{ old('expired_date') }}" class="form-control @error('expired_date') is-invalid @enderror">
@error('expired_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="minimum_order_amount">Minimum Order Amount</label>
<input type="number" step="any" name="minimum_order_amount" id="minimum_order_amount" value="{{ old('minimum_order_amount') }}" class="form-control @error('minimum_order_amount') is-invalid @enderror">
@error('minimum_order_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="usage_limit">Usage Limit</label>
<input type="number" step="any" name="usage_limit" id="usage_limit" value="{{ old('usage_limit') }}" class="form-control @error('usage_limit') is-invalid @enderror">
@error('usage_limit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
<label class="form-label" for="status">Status</label>
<select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
<option value="active" {{ (old('status')) == 'active' ? 'selected' : '' }}>Active</option>
<option value="inactive" {{ (old('status')) == 'inactive' ? 'selected' : '' }}>Inactive</option>
</select>
@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.coupon.index') }}" class="btn btn-light border">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div></div>
@endsection
