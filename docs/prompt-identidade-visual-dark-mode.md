# Prompt — Identidade Visual com Dark Mode (tenant_themes)

Cole este prompt no Claude Code (PhpStorm). Execute uma única vez.

---

```
Quero expandir o sistema de Identidade Visual do projeto Zenvyra para suportar:
- Dark mode: cores separadas para modo claro e modo escuro
- Mais elementos customizáveis: fundo da página, fundo da sidebar, fundo da topbar
- Tabela dedicada `tenant_themes` para as lojas (não mais colunas avulsas no tenant)
- Mesma expansão para o painel master (tabela `identidade_visual` já existe)

---

## LEIA ANTES DE COMEÇAR

Leia os seguintes arquivos para entender o estado atual:

1. `app/Models/IdentidadeVisual.php` — modelo singleton do painel master
2. `app/Services/BrandingService.php` — serviço de branding (cache + Filament colors + CSS vars)
3. `app/Filament/Admin/Pages/GestaoIdentidadeVisualPage.php` — page do admin master
4. `app/Filament/App/Pages/IdentidadeVisualLojaPage.php` — page do painel da loja
5. `resources/views/filament/hooks/branding-head.blade.php` — injeta CSS no admin
6. `resources/views/filament/hooks/branding-head-tenant.blade.php` — injeta CSS por tenant
7. `app/Providers/Filament/AppPanelProvider.php` — veja como `->colors()` usa o BrandingService
8. `app/Providers/Filament/AdminPanelProvider.php` — mesmo padrão para o admin
9. `database/migrations/2026_06_19_232345_create_identidade_visual_table.php` — schema atual do master
10. `database/migrations/2026_06_19_230928_add_brand_fields_to_te_tenants_table.php` — colunas legadas do tenant

---

## PASSO 1 — Migration: nova tabela `tenant_themes`

Crie uma migration para `tenant_themes` no schema `public` (não no schema do tenant).
O model usará `public.tenant_themes` como table (mesmo padrão de `Tenant::getTable()`).

Campos:
```php
$table->id();
$table->unsignedBigInteger('tenant_id')->unique(); // FK para te_tenants.id
$table->string('nome_loja', 80)->nullable();
$table->string('logo_path')->nullable();
$table->string('favicon_path')->nullable();

// Modo claro
$table->string('cor_primaria_light', 20)->default('#4F46E5');
$table->string('cor_sucesso_light', 20)->default('#10B981');
$table->string('cor_alerta_light', 20)->default('#F59E0B');
$table->string('cor_erro_light', 20)->default('#F43F5E');
$table->string('bg_page_light', 20)->nullable();       // null = padrão Filament
$table->string('bg_sidebar_light', 20)->nullable();
$table->string('bg_topbar_light', 20)->nullable();

// Modo escuro
$table->string('cor_primaria_dark', 20)->default('#818CF8');
$table->string('cor_sucesso_dark', 20)->default('#34D399');
$table->string('cor_alerta_dark', 20)->default('#FCD34D');
$table->string('cor_erro_dark', 20)->default('#FB7185');
$table->string('bg_page_dark', 20)->nullable();
$table->string('bg_sidebar_dark', 20)->nullable();
$table->string('bg_topbar_dark', 20)->nullable();

$table->timestamps();
```

Adicione FK: `$table->foreign('tenant_id')->references('id')->on('te_tenants')->cascadeOnDelete();`

---

## PASSO 2 — Migration: expandir `identidade_visual` com dark mode

Crie uma migration para adicionar as colunas de dark mode e backgrounds à tabela `identidade_visual` (já existente):

```php
$table->string('cor_primaria_dark', 20)->default('#818CF8')->after('cor_erro');
$table->string('cor_sucesso_dark', 20)->default('#34D399')->after('cor_primaria_dark');
$table->string('cor_alerta_dark', 20)->default('#FCD34D')->after('cor_sucesso_dark');
$table->string('cor_erro_dark', 20)->default('#FB7185')->after('cor_alerta_dark');
$table->string('bg_page_light', 20)->nullable()->after('cor_erro_dark');
$table->string('bg_sidebar_light', 20)->nullable()->after('bg_page_light');
$table->string('bg_topbar_light', 20)->nullable()->after('bg_sidebar_light');
$table->string('bg_page_dark', 20)->nullable()->after('bg_topbar_light');
$table->string('bg_sidebar_dark', 20)->nullable()->after('bg_page_dark');
$table->string('bg_topbar_dark', 20)->nullable()->after('bg_sidebar_dark');
```

---

## PASSO 3 — Model `TenantTheme`

Crie `app/Models/TenantTheme.php`:

```php
protected $table = 'tenant_themes';

// PostgreSQL: schema-qualify igual ao Tenant model
public function getTable(): string
{
    if ($this->getConnection()->getDriverName() === 'pgsql') {
        return 'public.tenant_themes';
    }
    return $this->table;
}
```

Fillable: todos os campos da migration. 
Casts: todos os campos `nullable()` sem cast especial (strings hex são suficientes).

Método estático:
```php
public static function obterOuCriarParaTenant(int $tenantId): self
{
    return static::firstOrCreate(
        ['tenant_id' => $tenantId],
        self::defaults()
    );
}

public static function defaults(): array
{
    return [
        'cor_primaria_light' => '#4F46E5',
        'cor_sucesso_light'  => '#10B981',
        'cor_alerta_light'   => '#F59E0B',
        'cor_erro_light'     => '#F43F5E',
        'cor_primaria_dark'  => '#818CF8',
        'cor_sucesso_dark'   => '#34D399',
        'cor_alerta_dark'    => '#FCD34D',
        'cor_erro_dark'      => '#FB7185',
    ];
}
```

Relationship no `Tenant` model:
```php
public function theme(): HasOne
{
    return $this->hasOne(TenantTheme::class, 'tenant_id');
}
```

---

## PASSO 4 — Atualizar `IdentidadeVisual` model

Adicione os novos campos ao `$fillable` e atualize `defaults()` com os valores dark mode e backgrounds:

```php
'cor_primaria_dark' => '#818CF8',
'cor_sucesso_dark'  => '#34D399',
'cor_alerta_dark'   => '#FCD34D',
'cor_erro_dark'     => '#FB7185',
'bg_page_light'     => null,
'bg_sidebar_light'  => null,
'bg_topbar_light'   => null,
'bg_page_dark'      => null,
'bg_sidebar_dark'   => null,
'bg_topbar_dark'    => null,
```

---

## PASSO 5 — Atualizar `BrandingService`

### 5a. Migrar dados do tenant de colunas legadas para `TenantTheme`

No método `dadosTenant(Tenant $tenant)`, substitua a leitura de `brand_name`, `brand_primary_color`, `brand_logo_path` direto no tenant por uma leitura do `TenantTheme`:

```php
private function dadosTenant(Tenant $tenant): array
{
    $tenantKey = $tenant->getKey();

    return Cache::remember(
        "branding.tenant.{$tenantKey}",
        now()->addHour(),
        static function () use ($tenantKey): array {
            $fresh = Tenant::find($tenantKey);
            if ($fresh === null) {
                return TenantTheme::defaults() + ['nome_loja' => '', 'logo_path' => null, 'favicon_path' => null];
            }

            $theme = TenantTheme::obterOuCriarParaTenant((int) $tenantKey);

            return [
                'nome_loja'          => $theme->nome_loja ?? $fresh->brand_name ?? $fresh->te_name,
                'logo_path'          => $theme->logo_path ?? $fresh->brand_logo_path,
                'favicon_path'       => $theme->favicon_path,
                'cor_primaria_light' => $theme->cor_primaria_light,
                'cor_sucesso_light'  => $theme->cor_sucesso_light,
                'cor_alerta_light'   => $theme->cor_alerta_light,
                'cor_erro_light'     => $theme->cor_erro_light,
                'cor_primaria_dark'  => $theme->cor_primaria_dark,
                'cor_sucesso_dark'   => $theme->cor_sucesso_dark,
                'cor_alerta_dark'    => $theme->cor_alerta_dark,
                'cor_erro_dark'      => $theme->cor_erro_dark,
                'bg_page_light'      => $theme->bg_page_light,
                'bg_sidebar_light'   => $theme->bg_sidebar_light,
                'bg_topbar_light'    => $theme->bg_topbar_light,
                'bg_page_dark'       => $theme->bg_page_dark,
                'bg_sidebar_dark'    => $theme->bg_sidebar_dark,
                'bg_topbar_dark'     => $theme->bg_topbar_dark,
            ];
        }
    );
}
```

### 5b. Atualizar `nomeTenant`, `logoHtmlTenant`, `coresFilamentTenant`

- `nomeTenant`: use `$dados['nome_loja']`
- `logoHtmlTenant`: use `$dados['logo_path']`
- `coresFilamentTenant`: use `$dados['cor_primaria_light']` como primary (Filament usa light mode por padrão)
  - Também use `cor_sucesso_light`, `cor_alerta_light`, `cor_erro_light`

### 5c. Novo método `gerarCssVariaveisTenant`

Expanda para incluir dark mode e backgrounds. O CSS gerado deve ser:

```css
/* Variáveis de base (light mode) */
:root,
[data-theme="light"] {
  --brand-primary:     #hex;
  --brand-success:     #hex;
  --brand-warning:     #hex;
  --brand-danger:      #hex;
  --brand-bg-page:     #hex;    /* omitir se null */
  --brand-bg-sidebar:  #hex;    /* omitir se null */
  --brand-bg-topbar:   #hex;    /* omitir se null */
}

/* Dark mode override */
[data-theme="dark"],
.dark {
  --brand-primary:     #hex;
  --brand-success:     #hex;
  --brand-warning:     #hex;
  --brand-danger:      #hex;
  --brand-bg-page:     #hex;    /* omitir se null */
  --brand-bg-sidebar:  #hex;    /* omitir se null */
  --brand-bg-topbar:   #hex;    /* omitir se null */
}
```

Para as cores `--color-primary-*` do Filament no dark mode, injete também:
```css
.dark {
  --color-primary-400: ... ; /* versão clara da cor dark */
  --color-primary-500: ... ; /* cor dark base */
  --color-primary-600: ... ; /* versão escura da cor dark */
}
```

Para converter um hex em shade 400/500/600, use `Color::hex($hex)` do Filament que retorna um array de shades — extraia os índices 400, 500, 600 e formate como `rgb(r g b)` (formato CSS moderno do Filament v5).

### 5d. Mesmo padrão para `gerarCssVariaveisMaster`

Expanda para incluir dark mode usando os campos dark da `IdentidadeVisual`. Mesmo formato de CSS acima.

---

## PASSO 6 — Atualizar `GestaoIdentidadeVisualPage` (admin master)

Substitua o formulário atual por um com **Tabs**:

**Tab "Geral":**
- `TextInput` para `nome_sistema`
- `FileUpload` para `logo_path`
- `FileUpload` para `favicon_path`

**Tab "Modo Claro":**
- `Section` "Cores" (columns 2): `ColorPicker` para `cor_primaria`, `cor_sucesso`, `cor_alerta`, `cor_erro`
- `Section` "Fundos" (columns 3): `ColorPicker` para `bg_page_light`, `bg_sidebar_light`, `bg_topbar_light` — todos com `->nullable()` e helper text "Deixe em branco para usar o padrão Filament"

**Tab "Modo Escuro":**
- `Section` "Cores" (columns 2): `ColorPicker` para `cor_primaria_dark`, `cor_sucesso_dark`, `cor_alerta_dark`, `cor_erro_dark`
- `Section` "Fundos" (columns 3): `ColorPicker` para `bg_page_dark`, `bg_sidebar_dark`, `bg_topbar_dark` — nullable, helper text igual

Use `Filament\Schemas\Components\Tabs` e `Filament\Schemas\Components\Tabs\Tab` do Filament v5.

No método `salvar()`: salve todos os campos novos junto com os existentes. Invalide o cache master.

---

## PASSO 7 — Atualizar `IdentidadeVisualLojaPage` (painel da loja)

Substitua o formulário atual por um com **Tabs**:

**Tab "Geral":**
- `TextInput` para `nome_loja` (label "Nome da loja no painel", maxLength 80)
- `FileUpload` para `logo_path` (disk 'public', directory dinâmico por tenant)
- `FileUpload` para `favicon_path` (disk 'public', directory dinâmico por tenant)

**Tab "Modo Claro":**
- `Section` "Cores" (columns 2): `ColorPicker` para `cor_primaria_light`, `cor_sucesso_light`, `cor_alerta_light`, `cor_erro_light`
- `Section` "Fundos" (columns 3): `ColorPicker` para `bg_page_light`, `bg_sidebar_light`, `bg_topbar_light` — nullable, helper "Deixe em branco para o padrão Filament"

**Tab "Modo Escuro":**
- `Section` "Cores" (columns 2): `ColorPicker` para `cor_primaria_dark`, `cor_sucesso_dark`, `cor_alerta_dark`, `cor_erro_dark`
- `Section` "Fundos" (columns 3): `ColorPicker` para `bg_page_dark`, `bg_sidebar_dark`, `bg_topbar_dark` — nullable

No `mount()`: carregue via `TenantTheme::obterOuCriarParaTenant($tenantId)->toArray()`.

No `salvar()`:
```php
$theme = TenantTheme::obterOuCriarParaTenant($tenantKey);
$theme->fill($dados)->save();
app(BrandingService::class)->invalidarCacheTenant($tenantKey);
```

---

## PASSO 8 — Colunas legadas no Tenant (opcional, manter compatibilidade)

NÃO remova as colunas `brand_name`, `brand_primary_color`, `brand_logo_path` do tenant.
O BrandingService já faz o fallback para elas quando `TenantTheme` ainda não tem os dados.
Apenas remova o fallback para as colunas legadas após confirmar que os dados foram migrados.

---

## PASSO 9 — Verificar views de branding-head

As views em `resources/views/filament/hooks/` já chamam `gerarCssVariaveisTenant` e `gerarCssVariaveisMaster`.
Após expandir esses métodos no `BrandingService`, as views não precisam de alteração.

Confirme que o `renderHook` de ambos os providers está apontando para as views corretas.

---

## PASSO 10 — Executar migrations e pint

```bash
php artisan migrate
vendor/bin/pint --dirty --format agent
```

---

## PASSO 11 — Testes

Escreva testes de feature para:

1. **`TenantThemeTest`**:
   - `obterOuCriarParaTenant` cria registro com defaults quando não existe
   - `obterOuCriarParaTenant` retorna o existente na segunda chamada
   - Salvar campos light e dark persiste corretamente

2. **`BrandingServiceTenantTest`**:
   - `coresFilamentTenant` retorna array com chave `primary` usando `cor_primaria_light`
   - `gerarCssVariaveisTenant` inclui bloco `:root` com `--brand-primary`
   - `gerarCssVariaveisTenant` inclui bloco `.dark` com as cores dark
   - Cache é invalidado após `invalidarCacheTenant`

3. **`IdentidadeVisualLojaPageTest`**:
   - Admin autenticado pode acessar a page
   - Salvar dados persiste no `TenantTheme` correto

```bash
php artisan test --compact --filter=TenantTheme
php artisan test --compact --filter=BrandingService
```

---

## RESUMO DO QUE DEVE EXISTIR NO FINAL

- `database/migrations/..._create_tenant_themes_table.php`
- `database/migrations/..._add_dark_mode_to_identidade_visual_table.php`
- `app/Models/TenantTheme.php` (schema-qualified, com `obterOuCriarParaTenant` e `defaults`)
- `app/Models/Tenant.php` — com `hasOne(TenantTheme::class)` adicionado
- `app/Models/IdentidadeVisual.php` — fillable e defaults atualizados
- `app/Services/BrandingService.php` — lê TenantTheme, gera CSS light+dark
- `app/Filament/Admin/Pages/GestaoIdentidadeVisualPage.php` — formulário com Tabs (Geral / Modo Claro / Modo Escuro)
- `app/Filament/App/Pages/IdentidadeVisualLojaPage.php` — mesmo padrão de Tabs
```
