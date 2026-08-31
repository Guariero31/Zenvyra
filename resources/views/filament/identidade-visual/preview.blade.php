@php
    $fields = $fields ?? [];
    $fallbackNome = $fallbackNome ?? 'Sua marca';
    $previewDesc = $previewDesc ?? 'Veja como nome e cores aparecem no painel. Alterne o modo claro/escuro do Filament para conferir os dois temas.';
    $navPrimary = $navPrimary ?? 'Dashboard';
    $navSecondary = $navSecondary ?? 'Configurações';
@endphp

<div
    class="pi-preview"
    x-data="{
        nome: @entangle('data.'.$fields['nome']).live,
        primaryLight: @entangle('data.'.$fields['primaryLight']).live,
        successLight: @entangle('data.'.$fields['successLight']).live,
        warningLight: @entangle('data.'.$fields['warningLight']).live,
        dangerLight: @entangle('data.'.$fields['dangerLight']).live,
        primaryDark: @entangle('data.'.$fields['primaryDark']).live,
        bgPageLight: @entangle('data.'.$fields['bgPageLight']).live,
        bgSidebarLight: @entangle('data.'.$fields['bgSidebarLight']).live,
        bgTopbarLight: @entangle('data.'.$fields['bgTopbarLight']).live,
        bgContentLight: @entangle('data.'.$fields['bgContentLight']).live,
        fallbackNome: @js($fallbackNome),
        displayNome() {
            return (this.nome && String(this.nome).trim()) ? this.nome : this.fallbackNome;
        },
        orDefault(value, fallback) {
            return (value && String(value).trim()) ? value : fallback;
        },
    }"
>
    <div class="pi-preview-header">
        <h3 class="pi-preview-title">Pré-visualização do painel</h3>
        <p class="pi-preview-desc">{{ $previewDesc }}</p>
    </div>

    <div class="pi-preview-modes">
        <div class="pi-preview-card">
            <span class="pi-preview-mode-label">Modo claro</span>
            <div
                class="pi-preview-shell pi-preview-shell-light"
                :style="{
                    '--pi-page': orDefault(bgPageLight, '#f8fafc'),
                    '--pi-sidebar': orDefault(bgSidebarLight, '#ffffff'),
                    '--pi-topbar': orDefault(bgTopbarLight, '#ffffff'),
                    '--pi-content': orDefault(bgContentLight, '#ffffff'),
                    '--pi-primary': orDefault(primaryLight, '#4F46E5'),
                    '--pi-success': orDefault(successLight, '#10B981'),
                    '--pi-warning': orDefault(warningLight, '#F59E0B'),
                    '--pi-danger': orDefault(dangerLight, '#F43F5E'),
                }"
            >
                <div class="pi-preview-layout">
                    <aside class="pi-preview-sidebar">
                        <span class="pi-preview-brand" x-text="displayNome()"></span>
                        <span class="pi-preview-nav-item pi-preview-nav-active">{{ $navPrimary }}</span>
                        <span class="pi-preview-nav-item">{{ $navSecondary }}</span>
                    </aside>
                    <div class="pi-preview-main">
                        <header class="pi-preview-topbar">
                            <span class="pi-preview-topbar-title">Dashboard</span>
                            <span class="pi-preview-btn">Salvar</span>
                        </header>
                        <main class="pi-preview-content">
                            <span class="pi-preview-chip pi-preview-chip-success">Ativo</span>
                            <span class="pi-preview-chip pi-preview-chip-warning">Em teste</span>
                            <span class="pi-preview-chip pi-preview-chip-danger">Inadimplente</span>
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <div class="pi-preview-card">
            <span class="pi-preview-mode-label">Modo escuro</span>
            <div
                class="pi-preview-shell pi-preview-shell-dark"
                :style="{
                    '--pi-primary': orDefault(primaryDark, '#818CF8'),
                }"
            >
                <div class="pi-preview-layout">
                    <aside class="pi-preview-sidebar">
                        <span class="pi-preview-brand" x-text="displayNome()"></span>
                        <span class="pi-preview-nav-item pi-preview-nav-active">{{ $navPrimary }}</span>
                    </aside>
                    <div class="pi-preview-main">
                        <header class="pi-preview-topbar">
                            <span class="pi-preview-topbar-title">Dashboard</span>
                            <span class="pi-preview-btn">Salvar</span>
                        </header>
                        <main class="pi-preview-content">
                            <span class="pi-preview-chip pi-preview-chip-primary">Ação principal</span>
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @include('filament.identidade-visual.preview-styles')
@endonce
