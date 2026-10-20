<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zenvyra — Gestão de lojas simplificada</title>
  <meta name="description" content="Catálogo, estoque e pedidos em um painel simples. Para lojas de todos os tamanhos.">

  <link rel="stylesheet" href="{{ asset('css/marketing.css') }}?v={{ filemtime(public_path('css/marketing.css')) }}">
  <script src="{{ asset('js/marketing.js') }}?v={{ filemtime(public_path('js/marketing.js')) }}" defer></script>
</head>
<body class="landing landing--light">
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

  <!-- NAV -->
  <nav class="nav" id="nav">
    <div class="nav-inner">
      <a href="{{ route('landing') }}" class="nav-logo">
        <div class="nav-logo-dot"></div>
        Zenvyra
      </a>
      <ul class="nav-links">
        <li><a href="#funcionalidades">Recursos</a></li>
        <li><a href="#como-funciona">Como funciona</a></li>
        <li><a href="#precos">Planos</a></li>
      </ul>
      <div class="nav-cta">
        <details class="landing-menu">
          <summary aria-label="Abrir menu"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></summary>
          <nav aria-label="Menu para celular">
            <a href="#funcionalidades">Recursos</a>
            <a href="#como-funciona">Como funciona</a>
            <a href="#precos">Planos</a>
            <a href="{{ url('/app/login') }}">Entrar</a>
          </nav>
        </details>
        <a href="{{ url('/app/login') }}" class="btn-ghost-nav">Entrar</a>
        <a href="{{ route('register.show') }}" class="btn-green">Começar avaliação</a>
      </div>
    </div>
  </nav>

  <main id="conteudo" tabindex="-1">
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero-inner">
      <div class="hero-text">
        <h1 id="hero-title"><span>Seu negócio cresce.</span><span>A <em>gestão</em> acompanha.</span></h1>
        <p class="hero-sub">Organize produtos, estoque e pedidos.<br>Cuide da sua loja com mais clareza.</p>
        <div class="hero-actions">
          <a href="{{ route('register.show') }}" class="btn-green-lg">Experimentar a Zenvyra <span aria-hidden="true">→</span></a>
          <a href="#como-funciona" class="btn-outline-white">Ver o sistema</a>
        </div>
        <p class="hero-reassurance">{{ config('subscriptions.evaluation_days') }} dias para avaliar. Sem cartão de crédito.</p>
      </div>
      <figure class="hero-mockup" aria-label="Prévia ilustrativa do painel da Zenvyra">
        <div class="preview-panel">
          <div class="preview-sidebar" aria-hidden="true">
            <div class="preview-brand"><span class="brand-dot"></span>Zenvyra</div>
            <div class="preview-nav"><span class="is-active"><svg viewBox="0 0 24 24"><path d="M3 10l9-7 9 7v11h-7v-7h-4v7H3z"/></svg><span>Início</span></span><span><svg viewBox="0 0 24 24"><path d="M12 2L3 7v10l9 5 9-5V7zM3 7l9 5 9-5M12 12v10"/></svg><span>Produtos</span></span><span><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 5V2h8v3M9 12h6"/></svg><span>Estoque</span></span><span><svg viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/></svg><span>Pedidos</span></span><span><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0116 0v3"/></svg><span>Clientes</span></span><span><svg viewBox="0 0 24 24"><path d="M3 15h4v7H3zM10 9h4v13h-4zM17 2h4v20h-4z"/></svg><span>Relatórios</span></span></div>
            <div class="preview-settings">Configurações</div>
          </div>
          <div class="preview-main">
            <div class="preview-heading"><strong>Estoque</strong><span>Últimos 30 dias <span aria-hidden="true">⌄</span></span></div>
            <div class="preview-stats">
              <div><span>Produtos no estoque</span><strong>482</strong><small>↑ 12%</small></div>
              <div><span>Entradas</span><strong>158</strong><small>↑ 8%</small></div>
              <div><span>Saídas</span><strong>129</strong><small>↑ 5%</small></div>
            </div>
            <div class="preview-chart-title">Movimentação de estoque</div>
            <svg class="preview-chart" viewBox="0 0 440 145" role="img" aria-label="Gráfico ilustrativo de movimentação de estoque">
              <defs><linearGradient id="chart-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#b6f542" stop-opacity=".35"/><stop offset="100%" stop-color="#b6f542" stop-opacity=".02"/></linearGradient></defs>
              <g stroke="#eceee6" stroke-width="1"><path d="M26 24H426M26 60H426M26 96H426M26 130H426"/></g>
              <path d="M26 118L48 95L70 98L92 89L114 103L136 92L158 80L180 90L202 89L224 65L246 74L268 68L290 75L312 47L334 47L356 28L378 35L400 20L426 26V130H26Z" fill="url(#chart-fill)"/>
              <path d="M26 118L48 95L70 98L92 89L114 103L136 92L158 80L180 90L202 89L224 65L246 74L268 68L290 75L312 47L334 47L356 28L378 35L400 20L426 26" fill="none" stroke="#7cac12" stroke-width="2" stroke-linejoin="round"/>
              <g fill="#73786b" font-size="9" font-family="system-ui"><text x="24" y="144">1 abr</text><text x="114" y="144">8 abr</text><text x="211" y="144">15 abr</text><text x="310" y="144">22 abr</text><text x="399" y="144">30 abr</text></g>
            </svg>
            <div class="preview-list-title">Produtos mais movimentados <span>Visão geral</span></div>
            <div class="preview-product"><span class="preview-product-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4l5 2 5-2 5 5-4 3-2-2v11H8V10l-2 2-4-3z"/></svg></span><div><strong>Camiseta Básica</strong><span>Roupas</span></div><span>124 un.</span><i style="--level:80%"></i></div>
            <div class="preview-product"><span class="preview-product-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14v14H5zM8 7V5a4 4 0 018 0v2"/></svg></span><div><strong>Bolsa Tote</strong><span>Acessórios</span></div><span>96 un.</span><i style="--level:60%"></i></div>
            <div class="preview-product"><span class="preview-product-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10l2 18h-6l-1-11-1 11H5z"/></svg></span><div><strong>Calça Slim</strong><span>Roupas</span></div><span>67 un.</span><i style="--level:40%"></i></div>
          </div>
        </div>
        <div class="preview-notification" aria-hidden="true"><span class="preview-check">✓</span><strong>Pedido registrado</strong><p>Tudo certo. Mais uma venda organizada.</p><span class="preview-notification-label">Um passo a menos na sua rotina.</span></div>
        <figcaption>Prévia ilustrativa do painel</figcaption>
      </figure>
    </div>
  </section>
  <section class="benefit-band" aria-label="O essencial para a sua loja">
    <div class="benefit-band__inner">
      <div class="benefit-item"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M6 11h20l2 18H4zM11 11V8a5 5 0 0110 0v3"/></svg><div><h2>Catálogo online</h2><p>Divulgue seus produtos e mantenha as informações sempre atualizadas.</p></div></div>
      <div class="benefit-item"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 2L3 9v14l13 7 13-7V9zM3 9l13 7 13-7M16 16v14"/></svg><div><h2>Estoque sob controle</h2><p>Saiba o que tem, o que está acabando e evite rupturas.</p></div></div>
      <div class="benefit-item"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M4 18h5v11H4zM14 11h5v18h-5zM24 3h5v26h-5z"/></svg><div><h2>Vendas organizadas</h2><p>Acompanhe pedidos, clientes e resultados em um só lugar.</p></div></div>
    </div>
  </section>
  <section class="editorial-intro" aria-labelledby="editorial-title">
    <div class="editorial-inner">
      <h2 id="editorial-title">Menos tarefas repetidas.<br>Mais espaço para vender.</h2>
      <div class="editorial-detail"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/></svg><h3>Pedidos mais simples</h3><p>Registre e acompanhe seus pedidos em poucos cliques, com mais agilidade no dia a dia.</p></div>
      <div class="editorial-detail"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8l10 10-8 8L3 11z"/><circle cx="7.5" cy="7.5" r="1"/></svg><h3>Produtos sempre organizados</h3><p>Cadastre produtos com variações, controle informações e mantenha tudo pronto para vender.</p></div>
    </div>
  </section>
  <!-- FEATURES -->
  <section class="features" id="funcionalidades">
    <div class="section-inner">
      <div class="section-header">
        <div class="section-tag">Funcionalidades</div>
        <h2 class="section-h2">Tudo que sua loja precisa,<br>sem o que não precisa.</h2>
        <p class="section-sub">Cada funcionalidade foi pensada para o dia a dia do lojista — sem complexidade desnecessária.</p>
      </div>
      <div class="features-grid">
        <div class="feature-card fade-up stagger-1">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h8"/></svg>
          </div>
          <h3 class="feature-h3">Catálogo organizado</h3>
          <p class="feature-p">Produtos, variações, fotos e categorias hierárquicas. Imagem de capa automática e badges de destaque.</p>
        </div>
        <div class="feature-card fade-up stagger-2">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
          </div>
          <h3 class="feature-h3">Estoque sob controle</h3>
          <p class="feature-p">Entradas, saídas, ajustes e alertas de baixo estoque. Histórico auditável com quem fez cada movimentação.</p>
        </div>
        <div class="feature-card fade-up stagger-3">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <h3 class="feature-h3">Pedidos do início ao fim</h3>
          <p class="feature-p">Do registro ao pagamento, com suporte completo a estorno total ou parcial por item e devolução ao estoque.</p>
        </div>
        <div class="feature-card fade-up stagger-4">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <h3 class="feature-h3">Clientes em foco</h3>
          <p class="feature-p">Cadastro com CPF/CNPJ, endereço e histórico de compras por cliente. Fácil de consultar na hora de vender.</p>
        </div>
        <div class="feature-card fade-up stagger-5">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
          </div>
          <h3 class="feature-h3">Um espaço para sua loja</h3>
          <p class="feature-p">Um ambiente próprio para sua loja, com seus produtos, sua equipe e seus dados organizados no mesmo lugar.</p>
        </div>
        <div class="feature-card fade-up stagger-6">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <h3 class="feature-h3">Equipe de vendas</h3>
          <p class="feature-p">Cadastre vendedores, atribua comissões por produto ou padrão da loja, e acompanhe a performance no dashboard.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="how" id="como-funciona">
    <div class="section-inner">
      <div class="section-header center">
        <div class="section-tag">Como funciona</div>
        <h2 class="section-h2">Comece a vender em minutos.</h2>
        <p class="section-sub">Sem configuração complexa. Sua loja fica pronta para operar logo após o cadastro.</p>
      </div>
      <div class="steps">
        <div class="step fade-up stagger-1">
          <div class="step-num">1</div>
          <h3>Crie sua loja</h3>
          <p>Preencha seus dados, dê um nome à sua loja e acesse seu painel. Tudo pelo navegador, sem instalar programas.</p>
        </div>
        <div class="step fade-up stagger-2">
          <div class="step-num">2</div>
          <h3>Monte seu catálogo</h3>
          <p>Cadastre produtos com fotos, variações e categorias. O controle de estoque já entra em operação automaticamente a cada venda.</p>
        </div>
        <div class="step fade-up stagger-3">
          <div class="step-num">3</div>
          <h3>Comece a vender</h3>
          <p>Registre pedidos, acompanhe o estoque em tempo real e veja os relatórios do mês no dashboard — tudo em um lugar só.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PRICING -->
  <section class="pricing" id="precos">
    <div class="section-inner">
      <div class="section-header center">
        <div class="section-tag">Preços</div>
        <h2 class="section-h2">Simples e previsível.</h2>
        <p class="section-sub">Comece no plano que faz sentido para o seu negócio. Mude quando precisar.</p>
      </div>
      <div class="pricing-grid">
        @php $stagger = 1; @endphp
        @foreach ($plans->reject(fn($p) => $p->slug === 'avaliacao') as $plan)
          @php
            $isFeatured = $plan->slug === 'pro';
            $features   = is_array($plan->features) ? $plan->features : (json_decode($plan->features ?? '[]', true) ?? []);

            $capacidade = [];
            if ($plan->max_products) {
                $capacidade[] = 'Até '.number_format($plan->max_products, 0, ',', '.').' produtos';
            } else {
                $capacidade[] = 'Produtos ilimitados';
            }
            if ($plan->max_users) {
                $capacidade[] = 'Até '.$plan->max_users.' usuário'.($plan->max_users > 1 ? 's' : '');
            } else {
                $capacidade[] = 'Usuários ilimitados';
            }
            if ($plan->trial_days) {
                $capacidade[] = $plan->trial_days.' dias grátis para testar';
            }
          @endphp
          <div class="pricing-card {{ $isFeatured ? 'featured' : '' }} fade-up stagger-{{ $stagger }}">
            @if ($isFeatured)
              <div class="pricing-badge">Plano Pro</div>
            @endif
            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-price"><sup>R$</sup>{{ number_format($plan->price, 2, ',', '.') }}</div>
            <div class="plan-period">por mês</div>
            <p class="plan-desc">{{ $plan->description }}</p>
            <hr class="plan-divider">
            <ul class="plan-features">
              @foreach ($capacidade as $item)
                <li>{{ $item }}</li>
              @endforeach
              @foreach ($features as $featureKey => $feature)
                @if (!in_array($featureKey, ['usuarios', 'produtos'], true) && is_scalar($feature))
                  <li>{{ is_string($featureKey) ? ucfirst(str_replace('_', ' ', $featureKey)).': ' : '' }}{{ $feature }}</li>
                @endif
              @endforeach
            </ul>
            <a href="{{ route('register.show', ['plano' => $plan->slug]) }}" class="plan-cta {{ $isFeatured ? 'primary' : 'secondary' }}">Conhecer plano {{ $plan->name }}</a>
          </div>
          @php $stagger++; @endphp
        @endforeach
      </div>
    </div>
  </section>

  <section class="faq" id="duvidas" aria-labelledby="faq-title">
    <div class="faq-inner">
      <div class="section-header center"><div class="section-tag">Antes de começar</div><h2 class="section-h2" id="faq-title">Dúvidas frequentes</h2></div>
      <details><summary>Preciso instalar algum programa?</summary><p>Não. Você acessa o painel pelo navegador para organizar seus produtos, estoque e pedidos.</p></details>
      <details><summary>Como funciona o catálogo online?</summary><p>Sua loja tem uma página própria para apresentar os produtos. Os clientes podem entrar em contato com você para combinar a compra.</p></details>
      <details><summary>O cadastro já contrata um plano?</summary><p>O cadastro inicia a avaliação da plataforma. Você conhece o sistema antes de escolher um plano para continuar.</p></details>
      <details><summary>Posso usar a identidade da minha loja?</summary><p>Sim. Você pode configurar o nome, as cores e o logotipo da sua loja no painel.</p></details>
    </div>
  </section>
  <!-- CTA FINAL -->
  <section class="cta-final">
    <div class="cta-final-inner">
      <h2>Sua loja pronta para operar <span>hoje.</span></h2>
      <p>Sem instalação, sem configuração. Comece o período de teste e veja a diferença.</p>
      <div class="cta-actions">
        <a href="{{ route('register.show') }}" class="btn-green-lg">Começar minha avaliação</a>
      </div>
    </div>
  </section>

  </main>
  <!-- FOOTER -->
  <footer>
    <div class="footer-inner">
      <div class="footer-logo">
        <div class="footer-logo-dot"></div>
        <span class="footer-logo-text">Zenvyra</span>
      </div>
      <div class="footer-links">
        <a href="{{ route('legal.terms') }}">Termos de uso</a>
        <a href="{{ route('legal.privacy') }}">Privacidade</a>
        <a href="mailto:contato@zenvyra.com.br">Contato</a>

      </div>
      <span class="footer-copy">© {{ date('Y') }} Zenvyra. Todos os direitos reservados.</span>
    </div>
  </footer>


</body>
</html>
