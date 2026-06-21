<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assinatura pendente — {{ $tenant->te_name }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f4f5;
            color: #18181b;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            padding: 32px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        h1 { font-size: 20px; margin: 20px 0 8px; }
        h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; color: #71717a; margin: 0 0 10px; }
        p { margin: 0 0 12px; line-height: 1.6; color: #3f3f46; }
        .store { font-weight: 600; color: #18181b; }
        .divider { height: 1px; background: #e4e4e7; margin: 24px 0; border: 0; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-primary { background: #4f46e5; color: #ffffff; }
        .btn-secondary { background: #ffffff; color: #3f3f46; border-color: #e4e4e7; }
        .logout-form { margin: 0; }
        .stores { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
        .store-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid #e4e4e7;
            border-radius: 10px;
            text-decoration: none;
            color: #18181b;
            font-weight: 600;
            font-size: 14px;
        }
        .store-link span.arrow { color: #a1a1aa; font-weight: 400; }
        .muted { font-size: 13px; color: #71717a; }
        @media (prefers-color-scheme: dark) {
            body { background: #18181b; color: #fafafa; }
            .card { background: #27272a; border-color: #3f3f46; }
            p { color: #d4d4d8; }
            .store { color: #fafafa; }
            .divider { background: #3f3f46; }
            .btn-secondary { background: #27272a; color: #d4d4d8; border-color: #3f3f46; }
            .store-link { border-color: #3f3f46; color: #fafafa; }
            .muted { color: #a1a1aa; }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Assinatura {{ $subscription?->status?->getLabel() ?? 'pendente' }}</span>

        @if ($isEvaluation)
            <h1>Período de avaliação encerrado</h1>

            <p>
                A avaliação da loja <span class="store">{{ $tenant->te_name }}</span> terminou
                e o painel ficou bloqueado.
            </p>

            <p>
                Gostou do que viu? Entre em contato para contratar um plano e continuar
                usando a plataforma com os dados que você cadastrou.
            </p>
        @else
            <h1>Acesso temporariamente indisponível</h1>

            <p>
                A loja <span class="store">{{ $tenant->te_name }}</span> está com a assinatura
                pendente e o painel de gestão ficou bloqueado até a regularização.
            </p>

            <p>
                Para reativar o acesso, entre em contato com a equipe responsável e
                confirme o pagamento da sua assinatura.
            </p>
        @endif

        @if ($otherTenants->isNotEmpty())
            <hr class="divider">
            <h2>Suas outras lojas</h2>
            <ul class="stores">
                @foreach ($otherTenants as $other)
                    <li>
                        <a class="store-link" href="{{ url('/app/'.$other->te_slug) }}">
                            {{ $other->te_name }}
                            <span class="arrow">Acessar &rarr;</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <hr class="divider">

        <div class="actions">
            <a class="btn btn-primary" href="mailto:suporte@zenvyra.com.br">Falar com o suporte</a>
            <form method="POST" action="{{ route('filament.app.auth.logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="btn btn-secondary">Sair da conta</button>
            </form>
        </div>

        <p class="muted" style="margin-top: 20px;">
            Já regularizou? Atualize esta página após a confirmação do pagamento.
        </p>
    </main>
</body>
</html>
