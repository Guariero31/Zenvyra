<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar minha loja — Zenvyra</title>
    <meta name="description" content="Comece a avaliação da Zenvyra e organize produtos, estoque e pedidos em um só lugar.">
    <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
    <script src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}" defer></script>
</head>
<body class="registration">
<a class="skip-link" href="#cadastro">Pular para o cadastro</a>

<header class="registration-header">
    <div class="registration-header__inner">
        <a href="{{ route('landing') }}" class="registration-logo"><span class="brand-dot" aria-hidden="true"></span>Zenvyra</a>
        <a href="{{ url('/app/login') }}" class="registration-header__link">Já tenho conta →</a>
    </div>
</header>
<main class="registration-main">
    <aside class="registration-aside">
        <p class="eyebrow">Sua próxima etapa começa aqui</p>
        <h1>Mais clareza para cuidar da sua loja.</h1>
        <p>Organize seu catálogo, acompanhe o estoque e gerencie pedidos em um só lugar.</p>
        <ul>
            <li><span aria-hidden="true">✓</span> Produtos sempre organizados</li>
            <li><span aria-hidden="true">✓</span> Estoque e vendas no mesmo painel</li>
            <li><span aria-hidden="true">✓</span> Um catálogo online com a sua marca</li>
        </ul>
        <p class="registration-note">Comece a avaliação sem informar cartão de crédito.</p>
    </aside>
    <section class="registration-card" id="cadastro" aria-labelledby="registration-title">
        <h2 id="registration-title">Crie sua loja</h2>
        <p class="registration-subtitle">Avalie por {{ config('subscriptions.evaluation_days') }} dias, sem cartão de crédito. Depois do cadastro, faça login para acessar seu painel.</p>
        @php
            $requestedPlan = request()->query('plano');
            $selectedPlan = is_string($requestedPlan) ? $plans->firstWhere('slug', $requestedPlan) : null;
        @endphp
        @if($selectedPlan)
            <p class="registration-subtitle">Seu interesse: <strong>{{ $selectedPlan->name }}</strong>. Você começa pela avaliação, antes de contratar um plano.</p>
        @endif
        @if (session('status'))
            <div class="alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error" role="alert">
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin-top:.4rem;padding-left:1.2rem">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" data-submit-label="Criando sua loja…">
            @csrf

            <div class="field">
                <label for="store_name">Nome da sua loja</label>
                <input
                    type="text"
                    id="store_name"
                    aria-invalid="{{ $errors->has('store_name') ? 'true' : 'false' }}"
                    aria-describedby="store_name-error"
                    name="store_name"
                    value="{{ old('store_name') }}"
                    placeholder="Ex: Moda Chic, Eletro Tech…"
                    class="{{ $errors->has('store_name') ? 'error' : '' }}"
                    autocomplete="organization"
                    required
                >
                <span id="store_name-error" class="field-error">@error('store_name'){{ $message }}@enderror</span>
            </div>

            <div class="field">
                <label for="name">Seu nome completo</label>
                <input
                    type="text"
                    id="name"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    aria-describedby="name-error"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Como você se chama?"
                    class="{{ $errors->has('name') ? 'error' : '' }}"
                    autocomplete="name"
                    required
                >
                <span id="name-error" class="field-error">@error('name'){{ $message }}@enderror</span>
            </div>

            <div class="field">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    aria-describedby="email-error"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="voce@sualoja.com.br"
                    class="{{ $errors->has('email') ? 'error' : '' }}"
                    autocomplete="email"
                    required
                >
                <span id="email-error" class="field-error">@error('email'){{ $message }}@enderror</span>
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <div class="password-input">
                <input
                    type="password"
                    id="password"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    aria-describedby="password-error"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    class="{{ $errors->has('password') ? 'error' : '' }}"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-controls="password" aria-label="Mostrar ou ocultar a senha" aria-pressed="false">Mostrar</button>
                </div>
                <span id="password-error" class="field-error">@error('password'){{ $message }}@enderror</span>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar senha</label>
                <div class="password-input">
                <input
                    type="password"
                    id="password_confirmation"
                    aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                    aria-describedby="password_confirmation-error"
                    name="password_confirmation"
                    placeholder="Repita a senha"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-label="Mostrar ou ocultar a confirmação de senha" aria-pressed="false">Mostrar</button>
                </div>
            </div>

            <span id="password_confirmation-error" class="field-error">@error('password_confirmation'){{ $message }}@enderror</span>
            <button type="submit" class="registration-submit">Criar minha loja →</button>
            <p class="registration-legal">Consulte nossos <a href="{{ route('legal.terms') }}">Termos de uso</a> e a <a href="{{ route('legal.privacy') }}">Política de privacidade</a>.</p>
        </form>

        <p class="registration-login">Já tem uma conta? <a href="{{ url('/app/login') }}">Fazer login</a></p>
    </section>
</main>
<footer class="registration-footer">© {{ date('Y') }} Zenvyra. Gestão de lojas simplificada.</footer>
</body>
</html>
