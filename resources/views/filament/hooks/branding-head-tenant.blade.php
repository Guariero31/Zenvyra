@php
    $service = app(\App\Services\BrandingService::class);
    $tenant  = \Filament\Facades\Filament::getTenant();
@endphp

@if ($tenant instanceof \App\Models\Tenant)
    {!! $service->gerarCssVariaveisTenant($tenant) !!}
@endif
