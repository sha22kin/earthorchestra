@extends('admin.layout', ['title' => 'My profile'])

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" class="form-narrow">
        @csrf
        @method('PUT')

        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="user" /> Profile</h2>
                <span class="pill pill--neutral">{{ $user->role_label }}</span>
            </header>
            <div class="card__body fields-2">
                <x-admin.field name="name" label="Full name" :value="$user->name" required />
                <x-admin.field name="email" label="Email" type="email" :value="$user->email" required />
            </div>
        </article>

        <article class="card">
            <header class="card__head"><h2><x-admin.icon name="shield" /> Change password</h2></header>
            <div class="card__body fields-2">
                <x-admin.field name="current_password" label="Current password" type="password" autocomplete="current-password" class="span-2" />
                <x-admin.field name="password" label="New password" type="password" autocomplete="new-password" hint="At least 10 characters." />
                <x-admin.field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />
            </div>
        </article>

        <div class="form-foot">
            <button type="submit" class="btn btn--primary"><x-admin.icon name="save" /> Save profile</button>
        </div>
    </form>
@endsection
