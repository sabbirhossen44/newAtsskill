@extends('admin.master_layout')
@section('title')
    <title>Edit Static Language | {{ setting('app_name', 'ATS Skill') }}</title>
@endsection
@section('admin-content')
    <h1 class="h4 mb-3">{{ __('Edit Static Language') }} — {{ $language->code ?? '' }}</h1>
    <div class="card"><div class="card-body">
        <form action="{{ route('admin.languages.update-static-languages', $language->code ?? '') }}" method="POST">@csrf
            <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Key') }}</th><th>{{ __('Value') }}</th></tr></thead>
            <tbody>@foreach ($translations ?? [] as $key => $value)<tr><td>{{ $key }}</td><td><input type="text" name="values[{{ $key }}]" value="{{ $value }}" class="form-control"></td></tr>@endforeach</tbody></table></div>
            <button class="btn btn-primary">{{ __('Save') }}</button></form>
    </div></div>
@endsection
