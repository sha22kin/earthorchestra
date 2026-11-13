@extends('admin.layout', ['title' => $user->exists ? 'Edit '.$user->name : 'Add person'])

@section('content')
    <a class="back" href="{{ route('admin.users.index') }}"><x-admin.icon name="chevron" class="flip" /> Admins &amp; editors</a>

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="form-narrow">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <article class="card">
            <header class="card__head"><h2>{{ $user->exists ? 'Account details' : 'New account' }}</h2></header>
            <div class="card__body fields-2">
                <x-admin.field name="name" label="Full name" :value="$user->name" required />
                <x-admin.field name="email" label="Email" type="email" :value="$user->email" autocomplete="off" required />
                <x-admin.field name="role" label="Role" type="select" :value="$user->role" :options="\App\Models\User::ROLES" class="span-2"
                               hint="Editors can change movements, pages and arrangements. Administrators can also manage people." />
                <x-admin.field name="password" label="{{ $user->exists ? 'New password' : 'Password' }}" type="password" autocomplete="new-password"
                               :required="! $user->exists" :hint="$user->exists ? 'Leave blank to keep the current password.' : 'At least 10 characters.'" />
                <x-admin.field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" :required="! $user->exists" />
            </div>
        </article>

        <div class="form-foot">
            <a class="btn btn--ghost" href="{{ route('admin.users.index') }}">Cancel</a>
            <button type="submit" class="btn btn--primary"><x-admin.icon name="save" /> {{ $user->exists ? 'Save' : 'Create account' }}</button>
        </div>
    </form>
@endsection
