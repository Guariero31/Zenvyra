@php
    use App\Models\IdentidadeVisualHistorico;

    $registros = IdentidadeVisualHistorico::query()
        ->where('painel', $painel)
        ->when($tenantId ?? null, fn ($query) => $query->where('tenant_id', $tenantId))
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
        if (str_ends_with($campo, '_dark')) {
            return 'escuro';
        }
        if (str_ends_with($campo, '_light')) {
            return 'claro';
        }
        if (in_array($campo, ['cor_primaria', 'cor_sucesso', 'cor_alerta', 'cor_erro'], true)) {
            return 'claro';
        }

        return 'geral';
    };

    $isHex = static fn (?string $v): bool => filled($v) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) === 1;

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

@once
    @include('filament.identidade-visual.historico-styles')
@endonce

<div class="pi-hist-section">
    <h3 class="pi-hist-title">Histórico de alterações</h3>
    <p class="pi-hist-desc">Registro de todas as modificações na identidade visual, com usuário responsável e data/hora.</p>

    @if (count($linhas) === 0)
        <div class="pi-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;margin-bottom:6px;opacity:.5" aria-hidden="true">
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
