# Zenvyra

SaaS multi-loja (multi-tenant) para gestão de catálogo e vendas, construído em Laravel + Filament. Cada loja é isolada fisicamente em seu próprio schema PostgreSQL, garantindo separação total de dados entre os clientes da plataforma.

## Visão geral

O sistema possui dois painéis distintos:

- **Painel Admin (SaaS)** — administração central da plataforma: lojas, usuários, planos e assinaturas.
- **Painel do Lojista (App)** — operação de cada loja: catálogo (categorias, produtos, variações, imagens), controle de estoque, clientes e pedidos, com fluxo completo de estorno e cancelamento.

## Arquitetura multi-tenant

O isolamento entre lojas é **físico**, via schemas do PostgreSQL (não há coluna `tenant_id`):

- **Schema central (`public`)** — dados compartilhados da plataforma: `users`, `te_tenants` (lojas), `tu_vinculos` (vínculo usuário↔loja com papel), `plans`, `subscriptions`, além de `cache`, `jobs` e `sessions`.
- **Schema por loja (`tenant_<slug>`)** — dados isolados de cada loja: `categories`, `products`, `product_variations`, `product_images`, `customers`, `orders`, `order_items`, `order_refunds`, `order_refund_items`, `stock_movements`.

Componentes que sustentam o modelo:

- `App\Models\Tenant` — registra a loja e expõe `te_slug` (identificador na URL) e `te_schema` (schema físico).
- `App\Observers\TenantObserver` — ao criar uma loja, gera `te_slug`/`te_schema` e provisiona o schema físico, rodando as migrations de tenant dentro dele.
- `App\Services\TenantSchemaManager` — cria o schema (`CREATE SCHEMA`) e executa `database/migrations/tenant`.
- `App\Support\PgsqlTenantSearchPath` + `App\Http\Middleware\SetTenantSearchPath` — apontam o `search_path` do PostgreSQL para o schema da loja resolvida na URL.
- `php artisan tenant:migrate` — roda as migrations de tenant em um schema específico ou em todas as lojas ativas.

### Ciclo de vida do pedido

Os pedidos seguem os status do enum `App\Enums\OrderStatus`: `pending`, `paid`, `partially_refunded`, `refunded`, `canceled`.

- A baixa de estoque ocorre uma única vez quando o pedido vira `paid` (controle por `inventory_deducted_at`).
- **Estorno** (`App\Services\OrderRefundManager`): total ou parcial por item, com devolução opcional ao estoque e trilha de auditoria (quem, quando, valor, motivo).
- **Cancelamento** (`App\Services\OrderLifecycleManager`): permitido apenas para pedidos `pending` (que ainda não baixaram estoque). Pedidos pagos devem ser estornados.

### Controle de estoque

O estoque segue um modelo híbrido:

- **Produto simples** — o saldo fica em `products.stock` (informado direto no cadastro do produto).
- **Produto com variações** — o saldo fica em cada `product_variations.stock`; o campo do produto é desabilitado e exibe o total somado.

Toda mutação de saldo passa pelo serviço `App\Services\StockManager`, que mantém um histórico auditável em `stock_movements` (enum `App\Enums\StockMovementType`: `entry`, `sale`, `refund`, `adjustment`):

- **Entrada manual** — ação *Entrada de estoque* no produto (ex.: compra de fornecedor), com quantidade, variação (quando houver) e observação.
- **Venda** — registrada automaticamente na baixa do pedido pago.
- **Estorno** — registrado automaticamente quando o estorno devolve itens ao estoque.

Recursos complementares:

- **Bloqueio de venda sem saldo** — o item do pedido valida a quantidade contra o estoque disponível (produto ou variação).
- **Alerta de estoque baixo** — coluna `low_stock_threshold` por produto, com badge colorido na listagem e filtro *Estoque baixo*.

## Stack

- PHP 8.3 · Laravel 13
- Filament 5 · Livewire 4 · Tailwind CSS 4
- PostgreSQL (isolamento por schema)
- [leandrocfe/filament-ptbr-form-fields](https://github.com/leandrocfe/filament-ptbr-form-fields) — campos pt-BR (R$, telefone)
- Testes com PHPUnit

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure a conexão PostgreSQL no `.env` (`DB_CONNECTION=pgsql`, host, porta, banco e credenciais). Em seguida:

```bash
php artisan migrate
php artisan db:seed
npm install
npm run dev
```

Para subir tudo de uma vez em desenvolvimento (server, queue, logs e Vite):

```bash
composer run dev
```

## Seed (base de teste)

`php artisan db:seed` popula a base completa de forma idempotente:

- Planos comerciais (Starter, Pro, Enterprise).
- Conta de administrador master e lojas de teste com seus lojistas e assinaturas.
- Catálogo com produtos simples (estoque direto) e com variações, além de vendas de cada loja, incluindo pedidos em todos os estados (pendente, pago, parcialmente estornado, estornado e cancelado).

## URLs e logins

> Os exemplos abaixo usam `http://127.0.0.1:8000` (padrão do `php artisan serve`). Se o seu ambiente usar outro host/porta (`APP_URL`), ajuste a base.

Senha de todas as contas de teste: `password`

### Painel Admin (plataforma)

- URL: `http://127.0.0.1:8000/admin`
- Login: `master@zenvyra.com.br`

### Painel do Lojista (lojas)

O login é único em `http://127.0.0.1:8000/app/login`. Após autenticar, a loja é resolvida pelo `slug` na URL.

| Loja | URL | Login |
|------|-----|-------|
| Loja Demo | `http://127.0.0.1:8000/app/loja-demo` | `lojista@zenvyra.com.br` |
| Moda Urbana | `http://127.0.0.1:8000/app/moda-urbana` | `contato@modaurbana.com.br` |
| Tech House | `http://127.0.0.1:8000/app/tech-house` | `contato@techhouse.com.br` |

A raiz (`/`) redireciona para o painel admin (`/admin`).

## Testes

```bash
php artisan test --compact
```

## Estrutura relevante

```
app/
├─ Console/Commands/TenantMigrateCommand.php   # tenant:migrate
├─ Enums/                                       # OrderStatus, OrderOrigin, TenantPortalRole, StockMovementType
├─ Filament/
│  ├─ Admin/Resources/                          # Tenants, Users, Plans, Subscriptions
│  └─ App/Resources/                            # Categories, Products (entrada de estoque + movimentações), Customers, Orders
├─ Http/Middleware/SetTenantSearchPath.php
├─ Models/                                       # ... Product, StockMovement
├─ Observers/                                    # Tenant, Order, OrderItem
├─ Policies/OrderPolicy.php                      # refund / cancel
├─ Services/                                     # TenantSchemaManager, OrderRefundManager, OrderLifecycleManager, StockManager
└─ Support/PgsqlTenantSearchPath.php

database/
├─ migrations/          # schema central
├─ migrations/tenant/   # schema de cada loja
├─ factories/
└─ seeders/             # PlanSeeder, DemoAccountsSeeder, TenantSeeder, TenantDataSeeder
```
