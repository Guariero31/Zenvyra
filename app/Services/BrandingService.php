<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IdentidadeVisual;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Support\ColorNormalizer;
use App\Support\PublicSchema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Throwable;

class BrandingService
{
    private const CACHE_KEY_MASTER = 'branding.master';

    private const DEFAULT_PRIMARY_LIGHT = '#4F46E5';

    private const DEFAULT_PRIMARY_DARK = '#818CF8';

    // -- Cache invalidation

    public function invalidarCacheMaster(): void
    {
        Cache::forget(self::CACHE_KEY_MASTER);
    }

    public function invalidarCacheTenant(int|string $tenantId): void
    {
        Cache::forget("branding.tenant.{$tenantId}");
    }

    // -- Master panel

    /** @return array<string, mixed> Plain array, safe to serialize in cache. */
    private function dadosMaster(): array
    {
        if (! $this->tabelaDisponivel()) {
            return IdentidadeVisual::defaults();
        }

        return Cache::remember(self::CACHE_KEY_MASTER, now()->addHour(), static function (): array {
            return IdentidadeVisual::obterOuCriar()->only([
                'nome_sistema',
                'cor_primaria', 'cor_sucesso', 'cor_alerta', 'cor_erro',
                'cor_primaria_dark', 'cor_sucesso_dark', 'cor_alerta_dark', 'cor_erro_dark',
                'bg_page_light', 'bg_content_light', 'bg_sidebar_light', 'bg_topbar_light',
                'bg_page_dark', 'bg_content_dark', 'bg_sidebar_dark', 'bg_topbar_dark',
                'logo_path', 'favicon_path',
            ]);
        });
    }

    public function nomeMaster(): string
    {
        return $this->dadosMaster()['nome_sistema'];
    }

    public function faviconUrlMaster(): ?string
    {
        $path = $this->dadosMaster()['favicon_path'];

        return filled($path) ? $this->resolverUrlArquivo($path) : null;
    }

    public function logoHtmlMaster(): ?HtmlString
    {
        $path = $this->dadosMaster()['logo_path'];

        if (! filled($path)) {
            return null;
        }

        $url = $this->resolverUrlArquivo($path);

        return new HtmlString('<img src="'.e($url).'" alt="Logo" class="h-8 w-auto object-contain" />');
    }

    /** @return array<string, mixed> */
    public function coresFilamentMaster(): array
    {
        $dados = $this->dadosMaster();

        return [
            'primary' => Color::hex($this->normalizarHex($dados['cor_primaria'])),
            'success' => Color::hex($this->normalizarHex($dados['cor_sucesso'])),
            'warning' => Color::hex($this->normalizarHex($dados['cor_alerta'])),
            'danger' => Color::hex($this->normalizarHex($dados['cor_erro'])),
            'info' => Color::Sky,
        ];
    }

    public function gerarCssVariaveisMaster(): string
    {
        $dados = $this->dadosMaster();

        return $this->gerarCssLightDark(
            light: [
                '--brand-primary' => $dados['cor_primaria'],
                '--brand-success' => $dados['cor_sucesso'],
                '--brand-warning' => $dados['cor_alerta'],
                '--brand-danger' => $dados['cor_erro'],
                '--brand-bg-page' => $dados['bg_page_light'] ?? null,
                '--brand-bg-content' => $dados['bg_content_light'] ?? null,
                '--brand-bg-sidebar' => $dados['bg_sidebar_light'] ?? null,
                '--brand-bg-topbar' => $dados['bg_topbar_light'] ?? null,
            ],
            dark: [
                '--brand-primary' => $dados['cor_primaria_dark'] ?? self::DEFAULT_PRIMARY_DARK,
                '--brand-success' => $dados['cor_sucesso_dark'] ?? '#34D399',
                '--brand-warning' => $dados['cor_alerta_dark'] ?? '#FCD34D',
                '--brand-danger' => $dados['cor_erro_dark'] ?? '#FB7185',
                '--brand-bg-page' => $dados['bg_page_dark'] ?? null,
                '--brand-bg-content' => $dados['bg_content_dark'] ?? null,
                '--brand-bg-sidebar' => $dados['bg_sidebar_dark'] ?? null,
                '--brand-bg-topbar' => $dados['bg_topbar_dark'] ?? null,
            ],
            darkPrimaryHex: $dados['cor_primaria_dark'] ?? self::DEFAULT_PRIMARY_DARK,
        );
    }

    // -- Tenant panel

    /**
     * @return array<string, mixed> Plain array, safe to serialize in cache.
     *
     * Reads from TenantTheme (dedicated table). When the table is not yet
     * available (pre-migration), obterOuCriarParaTenant returns an unsaved model
     * ($theme->exists === false) and we fall back to legacy brand_* columns.
     */
    private function dadosTenant(Tenant $tenant): array
    {
        $tenantKey = $tenant->getKey();

        return Cache::remember(
            "branding.tenant.{$tenantKey}",
            now()->addHour(),
            static function () use ($tenantKey): array {
                $fresh = Tenant::find($tenantKey);

                if ($fresh === null) {
                    return array_merge(TenantTheme::defaults(), [
                        'nome_loja' => '',
                        'logo_path' => null,
                        'favicon_path' => null,
                    ]);
                }

                $theme = TenantTheme::obterOuCriarParaTenant((int) $tenantKey);

                // Pre-migration fallback: table doesn't exist yet, model not persisted
                if (! $theme->exists) {
                    return [
                        'nome_loja' => $fresh->brand_name ?? $fresh->te_name,
                        'logo_path' => $fresh->brand_logo_path,
                        'favicon_path' => null,
                        'cor_primaria_light' => $fresh->brand_primary_color ?? self::DEFAULT_PRIMARY_LIGHT,
                        'cor_sucesso_light' => '#10B981',
                        'cor_alerta_light' => '#F59E0B',
                        'cor_erro_light' => '#F43F5E',
                        'bg_page_light' => null,
                        'bg_content_light' => null,
                        'bg_sidebar_light' => null,
                        'bg_topbar_light' => null,
                        'cor_primaria_dark' => self::DEFAULT_PRIMARY_DARK,
                        'cor_sucesso_dark' => '#34D399',
                        'cor_alerta_dark' => '#FCD34D',
                        'cor_erro_dark' => '#FB7185',
                        'bg_page_dark' => null,
                        'bg_content_dark' => null,
                        'bg_sidebar_dark' => null,
                        'bg_topbar_dark' => null,
                    ];
                }

                return [
                    'nome_loja' => $theme->nome_loja ?? $fresh->brand_name ?? $fresh->te_name,
                    'logo_path' => $theme->logo_path ?? $fresh->brand_logo_path,
                    'favicon_path' => $theme->favicon_path,
                    'cor_primaria_light' => $theme->cor_primaria_light,
                    'cor_sucesso_light' => $theme->cor_sucesso_light,
                    'cor_alerta_light' => $theme->cor_alerta_light,
                    'cor_erro_light' => $theme->cor_erro_light,
                    'bg_page_light' => $theme->bg_page_light,
                    'bg_content_light' => $theme->bg_content_light,
                    'bg_sidebar_light' => $theme->bg_sidebar_light,
                    'bg_topbar_light' => $theme->bg_topbar_light,
                    'cor_primaria_dark' => $theme->cor_primaria_dark,
                    'cor_sucesso_dark' => $theme->cor_sucesso_dark,
                    'cor_alerta_dark' => $theme->cor_alerta_dark,
                    'cor_erro_dark' => $theme->cor_erro_dark,
                    'bg_page_dark' => $theme->bg_page_dark,
                    'bg_content_dark' => $theme->bg_content_dark,
                    'bg_sidebar_dark' => $theme->bg_sidebar_dark,
                    'bg_topbar_dark' => $theme->bg_topbar_dark,
                ];
            }
        );
    }

    public function nomeTenant(Tenant $tenant): string
    {
        return $this->dadosTenant($tenant)['nome_loja'];
    }

    public function logoHtmlTenant(Tenant $tenant): ?HtmlString
    {
        $path = $this->dadosTenant($tenant)['logo_path'];

        if (! filled($path)) {
            return null;
        }

        $url = $this->resolverUrlArquivo($path);
        $nome = $this->nomeTenant($tenant);

        return new HtmlString('<img src="'.e($url).'" alt="'.e($nome).'" class="h-8 w-auto object-contain" />');
    }

    /** @return array<string, mixed> */
    public function coresFilamentTenant(Tenant $tenant): array
    {
        $dados = $this->dadosTenant($tenant);

        return [
            'primary' => Color::hex($this->normalizarHex($dados['cor_primaria_light'])),
            'success' => Color::hex($this->normalizarHex($dados['cor_sucesso_light'])),
            'warning' => Color::hex($this->normalizarHex($dados['cor_alerta_light'])),
            'danger' => Color::hex($this->normalizarHex($dados['cor_erro_light'])),
            'info' => Color::Sky,
        ];
    }

    public function gerarCssVariaveisTenant(Tenant $tenant): string
    {
        $dados = $this->dadosTenant($tenant);

        return $this->gerarCssLightDark(
            light: [
                '--brand-primary' => $dados['cor_primaria_light'],
                '--brand-success' => $dados['cor_sucesso_light'],
                '--brand-warning' => $dados['cor_alerta_light'],
                '--brand-danger' => $dados['cor_erro_light'],
                '--brand-bg-page' => $dados['bg_page_light'] ?? null,
                '--brand-bg-content' => $dados['bg_content_light'] ?? null,
                '--brand-bg-sidebar' => $dados['bg_sidebar_light'] ?? null,
                '--brand-bg-topbar' => $dados['bg_topbar_light'] ?? null,
            ],
            dark: [
                '--brand-primary' => $dados['cor_primaria_dark'],
                '--brand-success' => $dados['cor_sucesso_dark'],
                '--brand-warning' => $dados['cor_alerta_dark'],
                '--brand-danger' => $dados['cor_erro_dark'],
                '--brand-bg-page' => $dados['bg_page_dark'] ?? null,
                '--brand-bg-content' => $dados['bg_content_dark'] ?? null,
                '--brand-bg-sidebar' => $dados['bg_sidebar_dark'] ?? null,
                '--brand-bg-topbar' => $dados['bg_topbar_dark'] ?? null,
            ],
            darkPrimaryHex: $dados['cor_primaria_dark'],
        );
    }

    // -- Helpers

    /** Ensures any CSS color string is normalized to #RRGGBB before Color::hex(). */
    private function normalizarHex(string $valor): string
    {
        $normalizado = ColorNormalizer::hexFromAny($valor);

        // Last-resort fallback so Color::hex() never receives a non-hex value
        return preg_match('/^#[0-9a-fA-F]{6}$/', $normalizado)
            ? $normalizado
            : self::DEFAULT_PRIMARY_LIGHT;
    }

    private function tabelaDisponivel(): bool
    {
        try {
            return PublicSchema::hasTable('identidade_visual');
        } catch (Throwable) {
            return false;
        }
    }

    private function resolverUrlArquivo(string $path): string
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        } catch (Throwable) {
            // fallback below
        }

        return asset('storage/'.$path);
    }

    /**
     * Generates a <style> block with:
     *  - :root / .dark variable declarations
     *  - Filament element selectors that apply the bg variables with !important,
     *    overriding the Tailwind utility classes the framework uses by default.
     *    Only emitted when at least one mode (light or dark) has the value set.
     *
     * Null values in $light/$dark are omitted from the output.
     *
     * @param  array<string, string|null>  $light
     * @param  array<string, string|null>  $dark
     */
    private function gerarCssLightDark(array $light, array $dark, string $darkPrimaryHex): string
    {
        foreach ([
            '--brand-bg-page' => 'var(--panel-canvas, #f6f5f0)',
            '--brand-bg-content' => 'var(--panel-canvas, #f6f5f0)',
            '--brand-bg-sidebar' => 'var(--panel-sidebar, #fcfcf9)',
            '--brand-bg-topbar' => 'var(--panel-canvas, #f6f5f0)',
        ] as $variable => $fallback) {
            if (filled($light[$variable] ?? null) || filled($dark[$variable] ?? null)) {
                $light[$variable] ??= $fallback;
                $dark[$variable] ??= $fallback;
            }
        }

        $format = static function (array $vars): string {
            return collect($vars)
                ->filter(fn (?string $value): bool => filled($value))
                ->map(fn (string $value, string $key): string => "    {$key}: {$value};")
                ->implode("\n");
        };

        $lightCss = $format($light);
        $darkCss = $format($dark);

        $darkPalette = Color::hex($this->normalizarHex($darkPrimaryHex));
        $paletteCss = collect($darkPalette)
            ->map(fn (string $value, int|string $shade): string => "    --color-primary-{$shade}: {$value};")
            ->implode("\n");

        $html = "<style id=\"brand-vars\">\n";
        $html .= ":root,\n[data-theme=\"light\"] {\n{$lightCss}\n}\n\n";
        $html .= ".dark,\n[data-theme=\"dark\"] {\n{$darkCss}\n}\n\n";
        $html .= ".dark {\n{$paletteCss}\n}\n";

        // Apply background variables to Filament layout elements.
        // !important is required to override Filament's Tailwind utility classes
        // (bg-gray-50, bg-white, etc.) that are set directly on these elements.
        // Rules are only emitted when the variable is configured in at least one mode.
        if (filled($light['--brand-bg-page'] ?? null) || filled($dark['--brand-bg-page'] ?? null)) {
            $html .= "body.fi-body { background-color: var(--brand-bg-page) !important; }\n";
        }

        if (filled($light['--brand-bg-content'] ?? null) || filled($dark['--brand-bg-content'] ?? null)) {
            // The visible content canvas is `main.fi-main { background: #FAFAF8 }` defined in
            // the panel's custom theme.css — NOT .fi-main-ctn (which has no explicit bg).
            // We target main.fi-main with higher specificity to override the theme rule.
            $html .= "main.fi-main { background-color: var(--brand-bg-content) !important; }\n";
        }

        if (filled($light['--brand-bg-sidebar'] ?? null) || filled($dark['--brand-bg-sidebar'] ?? null)) {
            // Custom theme also sets .fi-sidebar { background-color: #FFF } (no !important).
            // .fi-sidebar-header-ctn has its own explicit bg in the Filament framework CSS.
            $html .= ".fi-sidebar,\n.fi-sidebar-header-ctn { background-color: var(--brand-bg-sidebar) !important; }\n";
        }

        if (filled($light['--brand-bg-topbar'] ?? null) || filled($dark['--brand-bg-topbar'] ?? null)) {
            // Custom theme also sets .fi-topbar { background-color: #FFF } (no !important).
            $html .= ".fi-topbar { background-color: var(--brand-bg-topbar) !important; }\n";
        }

        $html .= '</style>';

        return $html;
    }
}
