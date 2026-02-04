@if ($errors->any())
    <div class="alert alert-warning">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if (session('messageInfo'))
    <div class="alert alert-info">
        {{ session('messageInfo') }}
    </div>
@endif

@if (session('messageSuccess'))
    <div class="alert alert-success">
        {{ session('messageSuccess') }}
    </div>
@endif

@if (request()->routeIs('drivers.*') && session('messageSuccessDriver'))
    <div class="alert alert-success">
        {{ session('messageSuccessDriver') }}
    </div>
@endif

@if (session('messageDanger'))
    <div class="alert alert-danger">
        {{ session('messageDanger') }}
    </div>
@endif

@if (session('messageWarning'))
    <div class="alert alert-warning">
        {{ session('messageWarning') }}
    </div>
@endif
