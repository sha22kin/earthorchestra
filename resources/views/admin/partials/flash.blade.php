@if (session('status'))
    <div class="toast toast--ok" role="status" data-toast>
        <x-admin.icon name="check" />
        <span>{{ session('status') }}</span>
        <button type="button" class="toast__close" data-toast-close aria-label="Dismiss"><x-admin.icon name="x" /></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert--error" role="alert">
        <x-admin.icon name="alert" />
        <div>
            <strong>Please check the form.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
