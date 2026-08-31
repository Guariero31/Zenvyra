@php
    $service = app(\App\Services\BrandingService::class);
@endphp

{!! $service->gerarCssVariaveisMaster() !!}

@php $favicon = $service->faviconUrlMaster(); @endphp
@if ($favicon)
    <link rel="icon" href="{{ $favicon }}" />
@endif
