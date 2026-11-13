@extends('admin.layout', ['title' => 'Admins & editors'])

@section('action')
    <a class="btn btn--primary" href="{{ route('admin.users.create') }}"><x-admin.icon name="plus" /> <span>Add person</span></a>
@endsection

@section('content')
    <article class="card">
        <header class="card__head">
            <h2><x-admin.icon name="users" /> People with access</h2>
            <span class="muted small">Editors manage content · Administrators also manage people</span>
        </header>
        <div class="table-wrap">
            <table class="table table--roomy">
                <thead><tr><th>Name</th><th>Role</th><th>Last sign-in</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>
                                <span class="person">
                                    <span class="avatar avatar--sm">{{ $u->initials }}</span>
                                    <span>
                                        <span class="strong">{{ $u->name }}</span>
                                        @if ($u->is(auth()->user()))<span class="tag tag--blue">You</span>@endif
                                        <span class="muted small block">{{ $u->email }}</span>
                                    </span>
                                </span>
                            </td>
                            <td><span @class(['pill', 'pill--ok' => $u->isAdmin(), 'pill--neutral' => ! $u->isAdmin()])>{{ $u->role_label }}</span></td>
                            <td class="muted">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="actions">
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.users.edit', $u) }}"><x-admin.icon name="pencil" /> Edit</a>
                                @unless ($u->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Remove {{ $u->name }}? They won’t be able to sign in.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn--danger" title="Remove"><x-admin.icon name="trash" /></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection
