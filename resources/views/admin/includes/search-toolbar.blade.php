{{-- Toolbar de busca POST padrão das listagens --}}
@php
    $placeholder = $placeholder ?? 'Pesquisar…';
    $buttonLabel = $buttonLabel ?? 'Pesquisar';
@endphp
<div class="card-header ciop-toolbar">
    <form action="{{ $action }}" method="POST" class="ciop-search-form">
        @csrf
        <div class="input-group">
            <input type="text"
                class="form-control"
                name="filter"
                placeholder="{{ $placeholder }}"
                value="{{ $filters['filter'] ?? '' }}"
                aria-label="{{ $placeholder }}">
            <div class="input-group-append">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-search"></i>
                    <span class="d-none d-sm-inline">{{ $buttonLabel }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
