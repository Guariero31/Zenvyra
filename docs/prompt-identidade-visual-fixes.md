# Prompt — Fix: cores não aplicadas + histórico de alterações

Cole este prompt no Claude Code. Execute uma única vez.

---

```
Tenho dois problemas na feature de Identidade Visual que preciso corrigir, mais um requisito obrigatório de formato.

---

## REQUISITO OBRIGATÓRIO — ColorPicker sempre em hexadecimal

Em TODOS os `ColorPicker` de ambos os pages (`GestaoIdentidadeVisualPage` e `IdentidadeVisualLojaPage`), garanta que estejam configurados assim:

```php
ColorPicker::make('campo')
    ->label('...')
    ->rgba(false)   // força formato hex #RRGGBB, nunca rgba()
    ->hexColor()    // Filament v5: valida e formata como hex
    ->required()    // ou ->nullable() conforme o campo
```

Se `->hexColor()` não existir no Filament v5 instalado, use apenas `->rgba(false)`.

Verifique também que nenhum `ColorPicker` tem `->rgba(true)` explícito ou implícito. O valor persistido no banco deve ser sempre `#RRGGBB`.

---

## CONTEXTO — leia antes de começar

Leia os seguintes arquivos:
1. `app/Services/BrandingService.php`
2. `app/Filament/Admin/Pages/GestaoIdentidadeVisualPage.php`
3. `app/Filament/App/Pages/IdentidadeVisualLojaPage.php`
4. `app/Models/IdentidadeVisual.php`
5. `app/Models/TenantTheme.php`

---

## PROBLEMA 1 — Cores não estão sendo aplicadas

### Causa identificada

O `ColorPicker` do Filament pode retornar cores no formato `rgba(255, 0, 237, 1)` ao invés de hex `#FF00ED`.
O método `Color::hex()` do Filament espera exclusivamente strings no formato hex (`#RRGGBB`).
Quando recebe RGBA, o resultado é indefinido e as cores não são aplicadas.

### Verificação

Nos dois pages (`GestaoIdentidadeVisualPage` e `IdentidadeVisualLojaPage`), verifique se todos os `ColorPicker` têm `->rgba(false)`. Se não tiverem, adicione.

### Fix no BrandingService — normalização defensiva

No `BrandingService`, adicione um método privado `normalizarHex(string $valor): string` que:
1. Se o valor já começa com `#` e tem 7 chars → retorna como está
2. Se começa com `rgba(` ou `rgb(` → converte para hex:
   ```php
   private function normalizarHex(string $valor): string
   {
       $valor = trim($valor);

       // Já é hex válido
       if (preg_match('/^#[0-9a-fA-F]{6}$/', $valor)) {
           return $valor;
       }

       // rgba(r, g, b, a) ou rgb(r, g, b)
       if (preg_match('/rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $valor, $m)) {
           return sprintf('#%02x%02x%02x', (int)$m[1], (int)$m[2], (int)$m[3]);
       }

       // Fallback: cor padrão primária
       return '#4F46E5';
   }
   ```

Aplique `normalizarHex()` em TODOS os lugares onde um hex é passado para `Color::hex()` no `BrandingService`.
Também aplique na normalização antes de salvar no banco — use um método `saving` no model ou normalize nos métodos `salvar()` dos pages.

### Fix nos models — normalização no saving

No `IdentidadeVisual` e no `TenantTheme`, adicione um `boot()` com `static::saving()` que normaliza todos os campos de cor usando a mesma lógica acima (pode ser um trait `NormalizaCoresHex` compartilhado):

```php
protected static function boot(): void
{
    parent::boot();

    static::saving(static function (self $model): void {
        $camposCor = [
            'cor_primaria', 'cor_primaria_light', 'cor_primaria_dark',
            'cor_sucesso', 'cor_sucesso_light', 'cor_sucesso_dark',
            'cor_alerta', 'cor_alerta_light', 'cor_alerta_dark',
            'cor_erro', 'cor_erro_light', 'cor_erro_dark',
            'bg_page_light', 'bg_page_dark',
            'bg_sidebar_light', 'bg_sidebar_dark',
            'bg_topbar_light', 'bg_topbar_dark',
        ];

        foreach ($camposCor as $campo) {
            if (isset($model->{$campo}) && filled($model->{$campo})) {
                $model->{$campo} = self::hexFromAny($model->{$campo});
            }
        }
    });
}

private static function hexFromAny(string $valor): string
{
    $valor = trim($valor);
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $valor)) {
        return $valor;
    }
    if (preg_match('/rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $valor, $m)) {
        return sprintf('#%02x%02x%02x', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    return $valor;
}
```

Use apenas nos campos que efetivamente existem no model — não adicione campos que não existem na tabela.

### Invalidar cache após salvar

Verifique que os métodos `salvar()` de ambos os pages chamam `invalidarCacheMaster()` / `invalidarCacheTenant()` DEPOIS de salvar. Se o cache não for invalidado, o sistema continua servindo os valores antigos mesmo após salvar.

---

## PROBLEMA 2 — Histórico de alterações

### O que precisa existir

Uma tabela de histórico que registra cada vez que a identidade visual é alterada, mostrando:
- Quem fez a alteração (usuário)
- Quando (data/hora)
- Em qual painel (master ou qual loja)
- O que mudou: valores anteriores vs novos (apenas os campos que efetivamente mudaram)

### PASSO 2a — Migration: `identidade_visual_historico`

```php
Schema::create('identidade_visual_historico', function (Blueprint $table): void {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable(); // quem fez
    $table->string('painel', 20)->default('master'); // 'master' ou 'tenant'
    $table->unsignedBigInteger('tenant_id')->nullable(); // null para master
    $table->json('alteracoes'); // {"campo": {"de": "valor_antigo", "para": "valor_novo"}}
    $table->timestamp('criado_em')->useCurrent();
    // sem updated_at — histórico é imutável
});
```

Não adicione `->timestamps()` — use apenas `criado_em` imutável.

### PASSO 2b — Model `IdentidadeVisualHistorico`

Crie `app/Models/IdentidadeVisualHistorico.php`:

```php
class IdentidadeVisualHistorico extends Model
{
    public $timestamps = false;

    // schema-qualify igual aos outros models
    public function getTable(): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return 'public.identidade_visual_historico';
        }
        return $this->table;
    }

    protected $table = 'identidade_visual_historico';

    protected $fillable = ['user_id', 'painel', 'tenant_id', 'alteracoes', 'criado_em'];

    protected function casts(): array
    {
        return [
            'alteracoes' => 'array',
            'criado_em'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra uma entrada de histórico com apenas os campos que mudaram.
     *
     * @param array<string, mixed> $anterior Valores antes de salvar
     * @param array<string, mixed> $novo     Valores após salvar
     */
    public static function registrar(
        string $painel,
        array $anterior,
        array $novo,
        ?int $tenantId = null,
        ?int $userId = null,
    ): void {
        $alteracoes = [];

        foreach ($novo as $campo => $valorNovo) {
            $valorAntigo = $anterior[$campo] ?? null;
            if ($valorAntigo !== $valorNovo) {
                $alteracoes[$campo] = [
                    'de'   => $valorAntigo,
                    'para' => $valorNovo,
                ];
            }
        }

        if (empty($alteracoes)) {
            return; // nada mudou, não registra
        }

        static::create([
            'user_id'    => $userId ?? auth()->id(),
            'painel'     => $painel,
            'tenant_id'  => $tenantId,
            'alteracoes' => $alteracoes,
            'criado_em'  => now(),
        ]);
    }
}
```

### PASSO 2c — Registrar histórico nos pages

**Em `GestaoIdentidadeVisualPage::salvar()`**, antes de salvar:
```php
$anterior = IdentidadeVisual::obterOuCriar()->only([...campos...]);
// ... salva ...
$novo = $dados; // os dados do form
IdentidadeVisualHistorico::registrar('master', $anterior, $novo, null, auth()->id());
```

**Em `IdentidadeVisualLojaPage::salvar()`**, mesma lógica com `painel: 'tenant'` e `tenant_id: $tenantKey`.

### PASSO 2d — Seção de histórico no formulário

Em ambos os pages, adicione uma **seção de histórico abaixo das tabs** usando um `TableRepeater` ou, mais simples, um `ViewData` customizado.

A abordagem mais limpa no Filament v5 é adicionar um segundo schema via `getFooterSchemaComponents()` ou usar uma `Section` com `->schema([])` que renderiza uma View:

Crie a view `resources/views/filament/identidade-visual/historico.blade.php`:

```blade
@php
    use App\Models\IdentidadeVisualHistorico;
    $registros = IdentidadeVisualHistorico::query()
        ->where('painel', $painel)
        ->when($tenantId ?? null, fn ($q) => $q->where('tenant_id', $tenantId))
        ->with('user')
        ->orderByDesc('criado_em')
        ->limit(20)
        ->get();

    $labels = [
        'nome_sistema'       => 'Nome do sistema',
        'nome_loja'          => 'Nome da loja',
        'cor_primaria'       => 'Cor primária (claro)',
        'cor_primaria_light' => 'Cor primária (claro)',
        'cor_primaria_dark'  => 'Cor primária (escuro)',
        'cor_sucesso_light'  => 'Cor de sucesso (claro)',
        'cor_sucesso_dark'   => 'Cor de sucesso (escuro)',
        'cor_alerta_light'   => 'Cor de alerta (claro)',
        'cor_alerta_dark'    => 'Cor de alerta (escuro)',
        'cor_erro_light'     => 'Cor de erro (claro)',
        'cor_erro_dark'      => 'Cor de erro (escuro)',
        'bg_page_light'      => 'Fundo da página (claro)',
        'bg_page_dark'       => 'Fundo da página (escuro)',
        'bg_sidebar_light'   => 'Fundo da sidebar (claro)',
        'bg_sidebar_dark'    => 'Fundo da sidebar (escuro)',
        'bg_topbar_light'    => 'Fundo da topbar (claro)',
        'bg_topbar_dark'     => 'Fundo da topbar (escuro)',
        'logo_path'          => 'Logo',
        'favicon_path'       => 'Favicon',
    ];
@endphp

<div class="space-y-3">
    @forelse ($registros as $registro)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ $registro->user?->name ?? 'Sistema' }}
                </span>
                <span class="text-xs text-gray-400">
                    {{ $registro->criado_em->format('d/m/Y H:i') }}
                </span>
            </div>
            <div class="space-y-1.5">
                @foreach ($registro->alteracoes as $campo => $diff)
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500 dark:text-gray-400 min-w-0 shrink-0">
                            {{ $labels[$campo] ?? $campo }}
                        </span>
                        @if (str_starts_with($diff['de'] ?? '', '#'))
                            <span class="inline-block w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                  style="background:{{ $diff['de'] }}"></span>
                        @endif
                        <span class="text-gray-400 font-mono truncate">{{ $diff['de'] ?? '—' }}</span>
                        <span class="text-gray-300">→</span>
                        @if (str_starts_with($diff['para'] ?? '', '#'))
                            <span class="inline-block w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                  style="background:{{ $diff['para'] }}"></span>
                        @endif
                        <span class="text-gray-700 dark:text-gray-200 font-mono truncate">{{ $diff['para'] ?? '—' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 py-4 text-center">Nenhuma alteração registrada ainda.</p>
    @endforelse
</div>
```

Nos pages, adicione ao final do método `form(Schema $schema)` (dentro de `->components([...])`) um componente:

```php
Section::make('Histórico de alterações')
    ->description('Últimas 20 alterações nesta identidade visual.')
    ->collapsible()
    ->collapsed()
    ->schema([
        \Filament\Schemas\Components\View::make('filament.identidade-visual.historico')
            ->viewData([
                'painel'   => 'master', // ou 'tenant'
                'tenantId' => null,      // ou $tenantId para a loja
            ]),
    ]),
```

Para o page da loja, o `tenantId` vem de `Filament::getTenant()?->getKey()`.

---

## PASSO 3 — Executar migrations e pint

```bash
php artisan migrate
vendor/bin/pint --dirty --format agent
```

---

## PASSO 4 — Testes

Escreva testes para:

1. **`NormalizacaoCorTest`** (unit):
   - `rgba(255, 0, 237, 1)` → `#ff00ed`
   - `#4F46E5` → `#4F46E5` (sem alteração)
   - `rgb(16, 185, 129)` → `#10b981`
   - Valor vazio → não altera (ou retorna o mesmo)

2. **`IdentidadeVisualHistoricoTest`** (feature):
   - `registrar` não cria registro quando nada mudou
   - `registrar` cria registro com apenas os campos alterados
   - Campos de cor são normalizados para hex antes de salvar

```bash
php artisan test --compact --filter=NormalizacaoCor
php artisan test --compact --filter=IdentidadeVisualHistorico
```
```
