@php
    use App\Models\IdentidadeVisualHistorico;

    $registros = IdentidadeVisualHistorico::query()
        ->where('painel', $painel)
        ->when($tenantId ?? null, fn ($q) => $q->where('tenant_id', $tenantId))
        ->with('user')
        ->orderByDesc('criado_em')
        ->limit(50)
        ->get();

    $labels = [
        'nome_sistema'       => 'Nome do sistema',
        'nome_loja'          => 'Nome da loja',
        'cor_primaria'       => 'Cor primária',
        'cor_primaria_light' => 'Cor primária',
        'cor_primaria_dark'  => 'Cor primária',
        'cor_sucesso'        => 'Cor de sucesso',
        'cor_sucesso_light'  => 'Cor de sucesso',
        'cor_sucesso_dark'   => 'Cor de sucesso',
        'cor_alerta'         => 'Cor de alerta',
        'cor_alerta_light'   => 'Cor de alerta',
        'cor_alerta_dark'    => 'Cor de alerta',
        'cor_erro'           => 'Cor de erro',
        'cor_erro_light'     => 'Cor de erro',
        'cor_erro_dark'      => 'Cor de erro',
        'bg_page_light'      => 'Fundo da página',
        'bg_page_dark'       => 'Fundo da página',
        'bg_content_light'   => 'Fundo do conteúdo',
        'bg_content_dark'    => 'Fundo do conteúdo',
        'bg_sidebar_light'   => 'Fundo da sidebar',
        'bg_sidebar_dark'    => 'Fundo da sidebar',
        'bg_topbar_light'    => 'Fundo da topbar',
        'bg_topbar_dark'     => 'Fundo da topbar',
        'logo_path'          => 'Logotipo',
        'favicon_path'       => 'Favicon',
    ];

    $getTema = static function (string $campo): string {
        if (str_ends_with($campo, '_dark')) return 'escuro';
        if (str_ends_with($campo, '_light')) return 'claro';
        if (in_array($campo, ['cor_primaria', 'cor_sucesso', 'cor_alerta', 'cor_erro'], true)) return 'claro';
        return 'geral';
    };

    $isHex = static fn (?string $v): bool =>
        filled($v) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) === 1;

    $linhas = [];
    foreach ($registros as $registro) {
        foreach ($registro->alteracoes as $campo => $diff) {
            $linhas[] = [
                'criado_em' => $registro->criado_em,
                'user'      => $registro->user,
                'tema'      => $getTema($campo),
                'campo'     => $labels[$campo] ?? $campo,
                'de'        => $diff['de'] ?? null,
                'para'      => $diff['para'] ?? null,
            ];
        }
    }
@endphp

<style>
.pi-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    background: rgb(var(--color-primary-50));
    border: 1px solid rgb(var(--color-primary-200));
    margin-bottom: 1.5rem;
    font-size: 13px;
    color: rgb(var(--color-primary-800));
    line-height: 1.5;
}
.dark .pi-banner {
    background: rgba(var(--color-primary-400), 0.1);
    border-color: rgba(var(--color-primary-400), 0.25);
    color: rgb(var(--color-primary-300));
}
.pi-banner-icon {
    flex-shrink: 0;
    margin-top: 1px;
    color: rgb(var(--color-primary-500));
}
.dark .pi-banner-icon {
    color: rgb(var(--color-primary-400));
}

.pi-hist-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-section {
    border-top-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-title {
    font-size: 1rem;
    font-weight: 600;
    color: rgb(var(--color-gray-900));
    margin: 0 0 0.25rem;
}
.dark .pi-hist-title {
    color: rgb(var(--color-gray-100));
}
.pi-hist-desc {
    font-size: 12px;
    color: rgb(var(--color-gray-500));
    margin: 0 0 1.25rem;
}
.dark .pi-hist-desc {
    color: rgb(var(--color-gray-400));
}

.pi-hist-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px;
    border: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-wrap {
    border-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 760px;
}
.pi-hist-table thead tr {
    background: rgb(var(--color-gray-50));
    border-bottom: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-table thead tr {
    background: rgba(var(--color-gray-800), 0.5);
    border-bottom-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--color-gray-500));
    white-space: nowrap;
}
.dark .pi-hist-table th {
    color: rgb(var(--color-gray-400));
}
.pi-hist-table td {
    padding: 9px 14px;
    border-bottom: 1px solid rgb(var(--color-gray-100));
    color: rgb(var(--color-gray-700));
    vertical-align: middle;
    line-height: 1.45;
}
.dark .pi-hist-table td {
    border-bottom-color: rgba(var(--color-gray-700), 0.3);
    color: rgb(var(--color-gray-300));
}
.pi-hist-table tbody tr:last-child td {
    border-bottom: none;
}
.pi-hist-table tbody tr:hover td {
    background: rgb(var(--color-gray-50));
}
.dark .pi-hist-table tbody tr:hover td {
    background: rgba(var(--color-gray-700), 0.15);
}
.pi-col-data {
    white-space: nowrap;
    font-size: 12px;
    color: rgb(var(--color-gray-400));
}
.pi-col-autor { white-space: nowrap; font-weight: 500; }
.pi-col-campo { white-space: nowrap; font-weight: 600; }
.pi-col-valor { max-width: 200px; word-break: break-word; }

.pi-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 2px 9px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid transparent;
}
.pi-badge-claro {
    background: #fefce8;
    color: #854d0e;
    border-color: #fde047;
}
.dark .pi-badge-claro {
    background: rgba(234, 179, 8, 0.1);
    color: #fde047;
    border-color: rgba(234, 179, 8, 0.25);
}
.pi-badge-escuro {
    background: #eef2ff;
    color: #3730a3;
    border-color: #c7d2fe;
}
.dark .pi-badge-escuro {
    background: rgba(99, 102, 241, 0.12);
    color: #a5b4fc;
    border-color: rgba(99, 102, 241, 0.3);
}
.pi-badge-geral {
    background: rgb(var(--color-gray-100));
    color: rgb(var(--color-gray-600));
    border-color: rgb(var(--color-gray-300));
}
.dark .pi-badge-geral {
    background: rgba(var(--color-gray-700), 0.3);
    color: rgb(var(--color-gray-400));
    border-color: rgba(var(--color-gray-600), 0.4);
}

.pi-swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.12);
    vertical-align: middle;
    flex-shrink: 0;
}
.dark .pi-swatch { border-color: rgba(255,255,255,0.15); }
.pi-hex { font-family: ui-monospace, monospace; font-size: 12px; }
.pi-hex-novo { font-weight: 600; color: rgb(var(--color-gray-900)); }
.dark .pi-hex-novo { color: rgb(var(--color-gray-100)); }
.pi-em { color: rgb(var(--color-gray-300)); font-size: 13px; }
.dark .pi-em { color: rgb(var(--color-gray-600)); }

.pi-empty {
    padding: 32px 16px;
    text-align: center;
    border: 1px dashed rgb(var(--color-gray-300));
    border-radius: 8px;
    color: rgb(var(--color-gray-400));
    font-size: 13px;
}
.dark .pi-empty {
    border-color: rgba(var(--color-gray-600), 0.5);
    color: rgb(var(--color-gray-500));
}
</style>

{{-- Banner de aviso antes de salvar --}}
<div class="pi-banner">
    <svg class="pi-banner-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span>
        As alterações são aplicadas imediatamente após salvar. Todos os usuários do painel verão as mudanças na próxima página carregada.
    </span>
</div>

{{-- Histórico de alterações --}}
<div class="pi-hist-section">
    <h3 class="pi-hist-title">Histórico de alterações</h3>
    <p class="pi-hist-desc">Registro de todas as modificações na identidade visual, com usuário responsável e data/hora.</p>

    @if (count($linhas) === 0)
        <div class="pi-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;margin-bottom:6px;opacity:.5">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <br>Nenhuma alteração registrada ainda.
        </div>
    @else
        <div class="pi-hist-wrap">
            <table class="pi-hist-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Tema</th>
                        <th>Campo</th>
                        <th>Valor anterior</th>
                        <th>Valor novo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($linhas as $linha)
                        <tr>
                            <td class="pi-col-data">
                                {{ $linha['criado_em']->format('d/m/Y H:i') }}
                            </td>
                            <td class="pi-col-autor">
                                {{ $linha['user']?->name ?? 'Sistema' }}
                            </td>
                            <td>
                                @if ($linha['tema'] === 'claro')
                                    <span class="pi-badge pi-badge-claro">Modo Claro</span>
                                @elseif ($linha['tema'] === 'escuro')
                                    <span class="pi-badge pi-badge-escuro">Modo Escuro</span>
                                @else
                                    <span class="pi-badge pi-badge-geral">Geral</span>
                                @endif
                            </td>
                            <td class="pi-col-campo">{{ $linha['campo'] }}</td>
                            <td class="pi-col-valor">
                                @if ($isHex($linha['de']))
                                    <span style="display:inline-flex;align-items:center;gap:5px;">
                                        <span class="pi-swatch" style="background:{{ $linha['de'] }}"></span>
                                        <span class="pi-hex">{{ $linha['de'] }}</span>
                                    </span>
                                @elseif (filled($linha['de']))
                                    <span title="{{ $linha['de'] }}">{{ $linha['de'] }}</span>
                                @else
                                    <span class="pi-em">—</span>
                                @endif
                            </td>
                            <td class="pi-col-valor">
                                @if ($isHex($linha['para']))
                                    <span style="display:inline-flex;align-items:center;gap:5px;">
                                        <span class="pi-swatch" style="background:{{ $linha['para'] }}"></span>
                                        <span class="pi-hex pi-hex-novo">{{ $linha['para'] }}</span>
                                    </span>
                                @elseif (filled($linha['para']))
                                    <span class="pi-hex-novo" title="{{ $linha['para'] }}">{{ $linha['para'] }}</span>
                                @else
                                    <span class="pi-em">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
