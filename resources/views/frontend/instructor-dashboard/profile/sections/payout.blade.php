<div class="card mb-3"><div class="card-body"><h5>{{ __('Payout Information') }}</h5>
        <form action="{{ route('instructor.setting.payout.update') }}" method="POST">@csrf @method('PUT')
        <div class="row g-3">
        <div class="col-md-6"><label class="form-label">{{ __('Payment Method') }}</label><select name="payout_method" class="form-select"><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="rocket">Rocket</option><option value="bank">Bank Transfer</option></select></div>
        <div class="col-md-6"><label class="form-label">{{ __('Account Number') }}</label><input type="text" name="payout_account" value="{{ old('payout_account', $user->payout_account ?? '') }}" class="form-control"></div>
        </div><div class="mt-3"><button class="btn btn-primary">{{ __('Update Payout') }}</button></div></form></div></div>
