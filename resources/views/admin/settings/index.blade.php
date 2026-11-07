@extends('admin.layout')

@section('title', 'Settings')
@section('heading', 'Site Settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')

    <div class="admin-card">
        <h3>Email Recipients</h3>
        <div class="admin-field">
            <label for="admin_email">WordPress admin email</label>
            <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email', $emails['admin_email']) }}">
        </div>
        <div class="admin-field">
            <label for="ca_admin_email">Custom admin email (ca_admin_email)</label>
            <input type="email" id="ca_admin_email" name="ca_admin_email" value="{{ old('ca_admin_email', $emails['ca_admin_email']) }}">
        </div>
    </div>

    @include('admin.partials.acf-fields', [
        'acfScalars' => $acfScalars,
        'acfRepeaters' => $acfRepeaters,
    ])

    <button type="submit" class="admin-btn admin-btn-primary">Save settings</button>
</form>
<p class="admin-muted" style="margin-top:1rem">Global header/footer options from ACF (options_cal_*). Field keys match WordPress storage.</p>
@endsection