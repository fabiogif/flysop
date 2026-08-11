{{--
  Cabeçalho padrão de páginas admin (listagem / detalhe).

  Variáveis:
  - $title (string, obrigatório)
  - $subtitle (string|null)
  - $breadcrumbs (array de ['label' => '', 'url' => null|string])
  - $actionsHtml (string HTML opcional; preferir @section no caller quando complexo)
--}}
@php
    $breadcrumbs = $breadcrumbs ?? [];
    $subtitle = $subtitle ?? null;
@endphp
<div class="ciop-page-head">
    @if (count($breadcrumbs))
        <ol class="breadcrumb ciop-breadcrumb">
            @foreach ($breadcrumbs as $crumb)
                @if (!empty($crumb['url']))
                    <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                @else
                    <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                @endif
            @endforeach
        </ol>
    @endif
    <div class="ciop-page-head-row">
        <div class="ciop-page-head-titles">
            <h1 class="ciop-page-title">{{ $title }}</h1>
            @if ($subtitle)
                <p class="ciop-page-subtitle mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if (!empty($actionsHtml))
            <div class="ciop-page-actions">
                {!! $actionsHtml !!}
            </div>
        @endif
    </div>
</div>
