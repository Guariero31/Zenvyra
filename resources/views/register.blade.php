<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar minha loja — Zenvyra</title>
    <meta name="description" content="Crie sua loja online grátis. Comece agora e gerencie seu negócio de qualquer lugar.">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER ─────────────────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            text-decoration: none;
            letter-spacing: -.03em;
        }

        .logo span { color: #16a34a; }

        .header-link {
            font-size: .875rem;
            color: #64748b;
            text-decoration: none;
        }

        .header-link:hover { color: #1e293b; }

        /* ── MAIN ────────────────────────────────────────── */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: .25rem;
        }

        .card-subtitle {
            font-size: .9rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* ── FORM ────────────────────────────────────────── */
        .field { margin-bottom: 1.25rem; }

        label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .4rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: .65rem .875rem;
            font-size: .925rem;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            background: #fff;
            color: #1e293b;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,.12);
        }

        input.error { border-color: #ef4444; }

        .field-error {
            font-size: .8rem;
            color: #ef4444;
            margin-top: .35rem;
        }

        /* ── ALERT ───────────────────────────────────────── */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: .5rem;
            padding: .875rem 1rem;
            margin-bottom: 1.5rem;
            font-size: .875rem;
            color: #b91c1c;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .5rem;
            padding: .875rem 1rem;
            margin-bottom: 1.5rem;
            font-size: .875rem;
            color: #166534;
        }

        /* ── SUBMIT ──────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: .8rem;
            background: #16a34a;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: .5rem;
            cursor: pointer;
            transition: background .15s, transform .1s;
            margin-top: .25rem;
        }

        .btn-submit:hover { background: #15803d; }
        .btn-submit:active { transform: scale(.98); }

        /* ── DIVIDER ─────────────────────────────────────── */
        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: .8rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        /* ── FOOTER LINK ─────────────────────────────────── */
        .login-link {
            text-align: center;
            font-size: .875rem;
            color: #64748b;
        }

        .login-link a {
            color: #16a34a;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover { text-decoration: underline; }

        /* ── TRUST ───────────────────────────────────────── */
        .trust {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 1.25rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: .35rem;
            font-size: .78rem;
            color: #64748b;
        }

        .trust-icon { font-size: 1rem; }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.25rem; border-radius: .75rem; }
            .header { padding: 1rem; }
        }
    </style>
</head>
<body>

<header class="header">
    <a href="{{ route('landing') }}" class="logo">Zen<span>vyra</span></a>
    <a href="{{ url('/app/login') }}" class="header-link">Já tenho conta →</a>
</header>

<main class="main">
    <div class="card">
        <h1 class="card-title">Crie sua loja grátis</h1>
        <p class="card-subtitle">Pronta em instantes, sem cartão de crédito.</p>

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin-top:.4rem;padding-left:1.2rem">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="store_name">Nome da sua loja</label>
                <input
                    type="text"
                    id="store_name"
                    name="store_name"
                    value="{{ old('store_name') }}"
                    placeholder="Ex: Moda Chic, Eletro Tech…"
                    class="{{ $errors->has('store_name') ? 'error' : '' }}"
                    autocomplete="organization"
                    autofocus
                    required
                >
                @error('store_name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="name">Seu nome completo</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Como você se chama?"
                    class="{{ $errors->has('name') ? 'error' : '' }}"
                    autocomplete="name"
                    required
                >
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="voce@sualoja.com.br"
                    class="{{ $errors->has('email') ? 'error' : '' }}"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    class="{{ $errors->has('password') ? 'error' : '' }}"
                    autocomplete="new-password"
                    required
                >
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar senha</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Repita a senha"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="btn-submit">Criar minha loja →</button>
        </form>

        <div class="divider">ou</div>

        <p class="login-link">
            Já tem uma conta? <a href="{{ url('/app/login') }}">Fazer login</a>
        </p>

        <div class="trust">
            <span class="trust-item"><span class="trust-icon">🔒</span> Dados protegidos</span>
            <span class="trust-item"><span class="trust-icon">✅</span> Sem cartão de crédito</span>
            <span class="trust-item"><span class="trust-icon">🚀</span> Pronta em segundos</span>
        </div>
    </div>
</main>

</body>
</html>
