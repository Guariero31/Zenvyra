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

    $tema = static function (string $campo): string {
        if (str_ends_with($campo, '_light') || in_array($campo, ['cor_primaria', 'cor_sucesso', 'cor_alerta', 'cor_erro'], true)) {
            return 'Modo Claro';
        }
        if (str_ends_with($campo, '_dark')) {
            return 'Modo Escuro';
        }
        return 'Geral';
    };

    $isHex = static fn (?string $v): bool => filled($v) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) === 1;

    // Flatten: one row per changed field
    $linhas = [];
    foreach ($registros as $registro) {
        foreach ($registro->alteracoes as $campo => $diff) {
            $linhas[] = [
                'criado_em' => $registro->criado_em,
                'user'      => $registro->user,
                'tema'      => $tema($campo),
                'campo'     => $labels[$campo] ?? $campo,
                'de'        => $diff['de'] ?? null,
                'para'      => $diff['para'] ?? null,
            ];
        }
    }
@endphp

@if (count($linhas) === 0)
    <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
        Nenhuma alteração registrada ainda.
    </p>
@else
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Data/Hora</th>
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Usuário</th>
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tema</th>
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Campo</th>
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor anterior</th>
                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor novo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($linhas as $linha)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                            {{ $linha['criado_em']->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                            {{ $linha['user']?->name ?? 'Sistema' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                                'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20' => $linha['tema'] === 'Modo Claro',
                                'bg-indigo-50 text-indigo-700 ring-indigo-700/10 dark:bg-indigo-400/10 dark:text-indigo-400 dark:ring-indigo-400/20' => $linha['tema'] === 'Modo Escuro',
                                'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20' => $linha['tema'] === 'Geral',
                            ])>
                                {{ $linha['tema'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $linha['campo'] }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($isHex($linha['de']))
                                <span class="inline-flex items-center gap-1.5 font-mono text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-block w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-white/20 shrink-0"
                                          style="background: {{ $linha['de'] }}"></span>
                                    {{ $linha['de'] }}
                                </span>
                            @elseif (filled($linha['de']))
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px] block" title="{{ $linha['de'] }}">
                                    {{ $linha['de'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($isHex($linha['para']))
                                <span class="inline-flex items-center gap-1.5 font-mono text-xs text-gray-800 dark:text-gray-100 font-medium">
                                    <span class="inline-block w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-white/20 shrink-0"
                                          style="background: {{ $linha['para'] }}"></span>
                                    {{ $linha['para'] }}
                                </span>
                            @elseif (filled($linha['para']))
                                <span class="text-xs text-gray-800 dark:text-gray-100 font-medium truncate max-w-[200px] block" title="{{ $linha['para'] }}">
                                    {{ $linha['para'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
