<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zenvyra — Gestão de lojas simplificada</title>
  <meta name="description" content="Catálogo, estoque e pedidos em um painel simples. Para lojas de todos os tamanhos.">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green: #B6F542;
      --green-hover: #C8F85E;
      --green-mid: #5C9E0A;
      --green-tint: #F2FADF;
      --green-tint-2: #E6F5C4;
      --black: #0A0A0A;
      --dark: #111111;
      --dark-2: #1C1C1A;
      --white: #FFFFFF;
      --gray-50: #F8F8F5;
      --gray-100: #F0EFE8;
      --gray-200: #E4E2D8;
      --gray-400: #A8A69C;
      --gray-600: #6B6965;
      --gray-800: #2C2B28;
      --font: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
      --radius: 8px;
      --radius-lg: 12px;
      --radius-xl: 20px;
      --max-w: 1160px;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font);
      font-size: 16px;
      line-height: 1.65;
      color: var(--gray-800);
      background: var(--white);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    a { text-decoration: none; color: inherit; }
    img { display: block; max-width: 100%; }

    /* ── NAV ── */
    .nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      padding: 0 24px;
      transition: background 0.3s, border-color 0.3s;
    }
    .nav.scrolled {
      background: rgba(10,10,10,0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .nav-inner {
      max-width: var(--max-w);
      margin: 0 auto;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .nav-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 15px;
      font-weight: 500;
      color: var(--white);
    }
    .nav-logo-dot {
      width: 8px; height: 8px;
      background: var(--green);
      border-radius: 50%;
    }
    .nav-links {
      display: flex;
      align-items: center;
      gap: 32px;
      list-style: none;
    }
    .nav-links a {
      font-size: 14px;
      color: rgba(255,255,255,0.55);
      transition: color 0.2s;
    }
    .nav-links a:hover { color: var(--white); }
    .nav-cta {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .btn-ghost-nav {
      font-size: 14px;
      color: rgba(255,255,255,0.6);
      padding: 7px 14px;
      border-radius: var(--radius);
      transition: color 0.2s;
    }
    .btn-ghost-nav:hover { color: var(--white); }
    .btn-green {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--green);
      color: var(--black);
      font-size: 14px;
      font-weight: 500;
      padding: 8px 18px;
      border-radius: var(--radius);
      border: none;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
    }
    .btn-green:hover { background: var(--green-hover); transform: translateY(-1px); }
    .btn-green:active { transform: translateY(0); }

    /* ── HERO ── */
    .hero {
      background: var(--black);
      padding: 160px 24px 100px;
      position: relative;
      overflow: hidden;
    }
    .hero-glow {
      position: absolute;
      top: -80px; left: 50%;
      transform: translateX(-50%);
      width: 600px; height: 400px;
      background: radial-gradient(ellipse at center, rgba(182,245,66,0.12) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-inner {
      max-width: var(--max-w);
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: center;
    }
    .hero-text { position: relative; z-index: 1; }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(182,245,66,0.1);
      border: 1px solid rgba(182,245,66,0.2);
      color: var(--green);
      font-size: 12px;
      font-weight: 500;
      padding: 5px 12px;
      border-radius: 20px;
      margin-bottom: 24px;
      letter-spacing: 0.02em;
    }
    .hero-badge-dot { width: 6px; height: 6px; background: var(--green); border-radius: 50%; animation: pulse-dot 2s infinite; }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.4; }
    }
    .hero h1 {
      font-size: clamp(36px, 5vw, 60px);
      font-weight: 500;
      color: var(--white);
      line-height: 1.12;
      letter-spacing: -0.02em;
      margin-bottom: 20px;
    }
    .hero h1 span { color: var(--green); }
    .hero-sub {
      font-size: 18px;
      color: rgba(255,255,255,0.5);
      margin-bottom: 36px;
      max-width: 440px;
      line-height: 1.6;
    }
    .hero-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .btn-green-lg {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--green);
      color: var(--black);
      font-size: 15px;
      font-weight: 500;
      padding: 12px 24px;
      border-radius: var(--radius);
      border: none;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
    }
    .btn-green-lg:hover { background: var(--green-hover); transform: translateY(-1px); }
    .btn-outline-white {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: rgba(255,255,255,0.7);
      font-size: 15px;
      padding: 12px 24px;
      border-radius: var(--radius);
      border: 1px solid rgba(255,255,255,0.12);
      cursor: pointer;
      transition: border-color 0.2s, color 0.2s;
    }
    .btn-outline-white:hover { border-color: rgba(255,255,255,0.3); color: var(--white); }

    /* ── HERO MOCKUP ── */
    .hero-mockup {
      position: relative;
      z-index: 1;
    }
    .mockup-frame {
      background: #0F0F0F;
      border: 1px solid #222;
      border-radius: var(--radius-xl);
      overflow: hidden;
      box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
    }
    .mockup-bar {
      background: #0A0A0A;
      border-bottom: 1px solid #1A1A1A;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mockup-dot { width: 8px; height: 8px; border-radius: 50%; }
    .mockup-url {
      flex: 1;
      background: #1A1A1A;
      border-radius: 4px;
      height: 20px;
      margin: 0 8px;
    }
    .mockup-body { display: flex; height: 300px; }
    .mockup-sidebar {
      width: 140px;
      background: #0A0A0A;
      border-right: 1px solid #1A1A1A;
      padding: 12px 0;
      flex-shrink: 0;
    }
    .mockup-logo {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 0 12px 10px;
      border-bottom: 1px solid #1A1A1A;
      margin-bottom: 8px;
    }
    .mockup-logo-dot { width: 6px; height: 6px; background: var(--green); border-radius: 50%; }
    .mockup-logo-text { font-size: 10px; font-weight: 500; color: #fff; }
    .m-nav { display: flex; flex-direction: column; gap: 1px; padding: 0 8px; }
    .m-ni { display: flex; align-items: center; gap: 5px; padding: 5px 6px; border-radius: 5px; font-size: 9px; color: #444; }
    .m-ni.active { color: var(--green); background: rgba(182,245,66,0.08); }
    .m-ni-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .mockup-main { flex: 1; padding: 12px; overflow: hidden; }
    .m-topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .m-title { font-size: 11px; font-weight: 500; color: #fff; }
    .m-btn { background: var(--green); color: #000; font-size: 8px; font-weight: 500; padding: 3px 8px; border-radius: 4px; }
    .m-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 5px; margin-bottom: 10px; }
    .m-sc { background: #161616; border: 1px solid #1E1E1E; border-radius: 5px; padding: 6px 8px; }
    .m-sl { font-size: 7px; color: #444; margin-bottom: 2px; }
    .m-sv { font-size: 13px; font-weight: 500; color: #fff; }
    .m-table { background: #161616; border: 1px solid #1E1E1E; border-radius: 5px; overflow: hidden; }
    .m-tr { display: flex; justify-content: space-between; align-items: center; padding: 5px 8px; border-bottom: 1px solid #1E1E1E; }
    .m-tr:last-child { border-bottom: none; }
    .m-tr span:first-child { font-size: 8px; color: #bbb; }
    .m-badge { font-size: 7px; padding: 1px 6px; border-radius: 10px; font-weight: 500; }
    .m-badge.ok { background: #1a2e05; color: var(--green); }
    .m-badge.low { background: #2e1a05; color: #ef9f27; }
    .m-badge.out { background: #2e0505; color: #e24b4a; }

    /* ── SOCIAL PROOF ── */
    .social-proof {
      background: var(--gray-50);
      border-top: 1px solid var(--gray-200);
      border-bottom: 1px solid var(--gray-200);
      padding: 32px 24px;
    }
    .social-proof-inner {
      max-width: var(--max-w);
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 48px;
      flex-wrap: wrap;
    }
    .sp-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sp-value {
      font-size: 22px;
      font-weight: 500;
      color: var(--gray-800);
      letter-spacing: -0.02em;
    }
    .sp-value span { color: var(--green-mid); }
    .sp-label { font-size: 13px; color: var(--gray-600); }
    .sp-divider { width: 1px; height: 32px; background: var(--gray-200); }

    /* ── SECTIONS ── */
    section { padding: 96px 24px; }
    .section-inner { max-width: var(--max-w); margin: 0 auto; }
    .section-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 500;
      color: var(--green-mid);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      margin-bottom: 14px;
    }
    .section-tag::before {
      content: '';
      display: inline-block;
      width: 6px; height: 6px;
      background: var(--green-mid);
      border-radius: 50%;
    }
    .section-h2 {
      font-size: clamp(28px, 4vw, 42px);
      font-weight: 500;
      color: var(--gray-800);
      letter-spacing: -0.02em;
      line-height: 1.15;
      margin-bottom: 16px;
    }
    .section-sub {
      font-size: 18px;
      color: var(--gray-600);
      max-width: 520px;
      line-height: 1.6;
    }
    .section-header { margin-bottom: 56px; }
    .section-header.center { text-align: center; }
    .section-header.center .section-sub { margin: 0 auto; }

    /* ── FEATURES ── */
    .features { background: var(--white); }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }
    .feature-card {
      background: var(--gray-50);
      border: 1px solid var(--gray-200);
      border-radius: var(--radius-lg);
      padding: 28px;
      transition: border-color 0.2s, transform 0.2s;
    }
    .feature-card:hover {
      border-color: var(--green-tint-2);
      transform: translateY(-2px);
    }
    .feature-icon {
      width: 40px; height: 40px;
      background: var(--green-tint);
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }
    .feature-icon svg { width: 20px; height: 20px; stroke: var(--green-mid); fill: none; stroke-width: 1.75; stroke-linecap: round; stroke-linejoin: round; }
    .feature-h3 {
      font-size: 16px;
      font-weight: 500;
      color: var(--gray-800);
      margin-bottom: 8px;
      letter-spacing: -0.01em;
    }
    .feature-p {
      font-size: 14px;
      color: var(--gray-600);
      line-height: 1.6;
    }

    /* ── HOW IT WORKS ── */
    .how { background: var(--gray-50); border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200); }
    .steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0;
    }
    .step {
      padding: 32px;
      position: relative;
    }
    .step:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 48px; right: 0;
      width: 1px; height: 48px;
      background: var(--gray-200);
    }
    .step-num {
      width: 36px; height: 36px;
      background: var(--green);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 500;
      color: var(--black);
      margin-bottom: 16px;
    }
    .step h3 {
      font-size: 17px;
      font-weight: 500;
      color: var(--gray-800);
      margin-bottom: 8px;
      letter-spacing: -0.01em;
    }
    .step p {
      font-size: 14px;
      color: var(--gray-600);
      line-height: 1.6;
    }

    /* ── PRICING ── */
    .pricing { background: var(--white); }
    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      align-items: start;
    }
    .pricing-card {
      border: 1px solid var(--gray-200);
      border-radius: var(--radius-lg);
      padding: 28px;
      background: var(--white);
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .pricing-card:hover {
      border-color: var(--gray-400);
    }
    .pricing-card.featured {
      border: 2px solid var(--green);
      position: relative;
    }
    .pricing-badge {
      position: absolute;
      top: -12px; left: 50%;
      transform: translateX(-50%);
      background: var(--green);
      color: var(--black);
      font-size: 11px;
      font-weight: 500;
      padding: 3px 12px;
      border-radius: 20px;
      white-space: nowrap;
    }
    .plan-name {
      font-size: 13px;
      font-weight: 500;
      color: var(--gray-600);
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-bottom: 12px;
    }
    .plan-price {
      font-size: 38px;
      font-weight: 500;
      color: var(--gray-800);
      letter-spacing: -0.03em;
      line-height: 1;
      margin-bottom: 4px;
    }
    .plan-price sup { font-size: 18px; vertical-align: super; font-weight: 400; }
    .plan-period { font-size: 13px; color: var(--gray-400); margin-bottom: 20px; }
    .plan-desc { font-size: 14px; color: var(--gray-600); margin-bottom: 20px; line-height: 1.5; }
    .plan-divider { border: none; border-top: 1px solid var(--gray-200); margin-bottom: 20px; }
    .plan-features { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
    .plan-features li {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      font-size: 14px;
      color: var(--gray-700);
    }
    .plan-features li::before {
      content: '';
      width: 16px; height: 16px;
      background: var(--green-tint);
      border-radius: 50%;
      flex-shrink: 0;
      margin-top: 2px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M3 8l3.5 3.5L13 5' stroke='%235C9E0A' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
      background-size: cover;
    }
    .plan-cta { display: block; text-align: center; padding: 11px; border-radius: var(--radius); font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; }
    .plan-cta.primary { background: var(--green); color: var(--black); }
    .plan-cta.primary:hover { background: var(--green-hover); }
    .plan-cta.secondary { background: transparent; color: var(--gray-800); border: 1px solid var(--gray-200); }
    .plan-cta.secondary:hover { border-color: var(--gray-400); }

    /* ── CTA FINAL ── */
    .cta-final {
      background: var(--black);
      padding: 100px 24px;
      text-align: center;
    }
    .cta-final-inner { max-width: 640px; margin: 0 auto; }
    .cta-final h2 {
      font-size: clamp(28px, 4vw, 46px);
      font-weight: 500;
      color: var(--white);
      letter-spacing: -0.02em;
      line-height: 1.15;
      margin-bottom: 16px;
    }
    .cta-final h2 span { color: var(--green); }
    .cta-final p { font-size: 17px; color: rgba(255,255,255,0.45); margin-bottom: 36px; }
    .cta-actions { display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap; }

    /* ── FOOTER ── */
    footer {
      background: var(--dark-2);
      border-top: 1px solid rgba(255,255,255,0.06);
      padding: 48px 24px;
    }
    .footer-inner {
      max-width: var(--max-w);
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 24px;
    }
    .footer-logo { display: flex; align-items: center; gap: 7px; }
    .footer-logo-dot { width: 7px; height: 7px; background: var(--green); border-radius: 50%; }
    .footer-logo-text { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.7); }
    .footer-copy { font-size: 13px; color: rgba(255,255,255,0.25); }
    .footer-links { display: flex; gap: 24px; }
    .footer-links a { font-size: 13px; color: rgba(255,255,255,0.35); transition: color 0.2s; }
    .footer-links a:hover { color: rgba(255,255,255,0.7); }

    /* ── ANIMATIONS ── */
    .fade-up {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }
    .fade-in {
      opacity: 0;
      transition: opacity 0.6s ease;
    }
    .fade-in.visible { opacity: 1; }

    .stagger-1 { transition-delay: 0.05s; }
    .stagger-2 { transition-delay: 0.1s; }
    .stagger-3 { transition-delay: 0.15s; }
    .stagger-4 { transition-delay: 0.2s; }
    .stagger-5 { transition-delay: 0.25s; }
    .stagger-6 { transition-delay: 0.3s; }

    @media (prefers-reduced-motion: reduce) {
      .fade-up, .fade-in { opacity: 1 !important; transform: none !important; transition: none !important; }
    }

    /* ── MOBILE ── */
    @media (max-width: 900px) {
      .hero-inner { grid-template-columns: 1fr; gap: 48px; }
      .hero-mockup { display: none; }
      .features-grid { grid-template-columns: 1fr 1fr; }
      .steps { grid-template-columns: 1fr; }
      .step:not(:last-child)::after { display: none; }
      .pricing-grid { grid-template-columns: 1fr; }
      .nav-links { display: none; }
      .sp-divider { display: none; }
    }
    @media (max-width: 600px) {
      section { padding: 64px 20px; }
      .hero { padding: 120px 20px 72px; }
      .features-grid { grid-template-columns: 1fr; }
      .social-proof-inner { gap: 24px; }
    }
  </style>
</head>
<body>

  <!-- NAV -->
  <nav class="nav" id="nav">
    <div class="nav-inner">
      <a href="/" class="nav-logo">
        <div class="nav-logo-dot"></div>
        Zenvyra
      </a>
      <ul class="nav-links">
        <li><a href="#funcionalidades">Funcionalidades</a></li>
        <li><a href="#como-funciona">Como funciona</a></li>
        <li><a href="#precos">Preços</a></li>
      </ul>
      <div class="nav-cta">
        <a href="{{ url('/app/login') }}" class="btn-ghost-nav">Entrar</a>
        <a href="{{ route('register.show') }}" class="btn-green">Começar grátis</a>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-glow"></div>
    <div class="hero-inner">
      <div class="hero-text">
        <div class="hero-badge">
          <div class="hero-badge-dot"></div>
          Plataforma multi-loja
        </div>
        <h1>Gerencie sua loja com <span>mais clareza.</span></h1>
        <p class="hero-sub">Catálogo, estoque e pedidos em um painel simples — para lojistas que querem mais tempo para vender.</p>
        <div class="hero-actions">
          <a href="{{ route('register.show') }}" class="btn-green-lg">Começar grátis</a>
          <a href="#como-funciona" class="btn-outline-white">Ver como funciona</a>
        </div>
      </div>
      <div class="hero-mockup">
        <div class="mockup-frame">
          <div class="mockup-bar">
            <div class="mockup-dot" style="background:#ff5f57"></div>
            <div class="mockup-dot" style="background:#febc2e"></div>
            <div class="mockup-dot" style="background:#28c840"></div>
            <div class="mockup-url"></div>
          </div>
          <div class="mockup-body">
            <div class="mockup-sidebar">
              <div class="mockup-logo">
                <div class="mockup-logo-dot"></div>
                <span class="mockup-logo-text">Zenvyra</span>
              </div>
              <div class="m-nav">
                <div class="m-ni"><div class="m-ni-dot"></div>Dashboard</div>
                <div class="m-ni"><div class="m-ni-dot"></div>Pedidos</div>
                <div class="m-ni"><div class="m-ni-dot"></div>Catálogo</div>
                <div class="m-ni active"><div class="m-ni-dot"></div>Estoque</div>
                <div class="m-ni"><div class="m-ni-dot"></div>Clientes</div>
                <div class="m-ni"><div class="m-ni-dot"></div>Vendedores</div>
              </div>
            </div>
            <div class="mockup-main">
              <div class="m-topbar">
                <span class="m-title">Estoque</span>
                <span class="m-btn">+ Entrada</span>
              </div>
              <div class="m-stats">
                <div class="m-sc"><div class="m-sl">Total</div><div class="m-sv">142</div></div>
                <div class="m-sc"><div class="m-sl">Baixo</div><div class="m-sv" style="color:#ef9f27">8</div></div>
                <div class="m-sc"><div class="m-sl">Zerado</div><div class="m-sv" style="color:#e24b4a">3</div></div>
              </div>
              <div class="m-table">
                <div class="m-tr"><span>Camiseta Básica Preta</span><span class="m-badge ok">47 un.</span></div>
                <div class="m-tr"><span>Calça Slim Fit</span><span class="m-badge low">6 un.</span></div>
                <div class="m-tr"><span>Tênis Runner Pro</span><span class="m-badge out">0 un.</span></div>
                <div class="m-tr"><span>Boné Snapback</span><span class="m-badge ok">23 un.</span></div>
                <div class="m-tr"><span>Mochila Urban</span><span class="m-badge ok">31 un.</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SOCIAL PROOF -->
  <div class="social-proof">
    <div class="social-proof-inner">
      <div class="sp-item">
        <div>
          <div class="sp-value"><span>+</span>{{ $stats['tenants'] }}</div>
          <div class="sp-label">lojas ativas</div>
        </div>
      </div>
      <div class="sp-divider"></div>
      <div class="sp-item">
        <div>
          <div class="sp-value">99<span>.9%</span></div>
          <div class="sp-label">de uptime</div>
        </div>
      </div>
      <div class="sp-divider"></div>
      <div class="sp-item">
        <div>
          <div class="sp-value"><span>+</span>{{ number_format($stats['orders'], 0, ',', '.') }}</div>
          <div class="sp-label">pedidos processados</div>
        </div>
      </div>
      <div class="sp-divider"></div>
      <div class="sp-item">
        <div>
          <div class="sp-value">100<span>%</span></div>
          <div class="sp-label">suporte em PT-BR</div>
        </div>
      </div>
    </div>
  </div>

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
          <h3 class="feature-h3">Multi-loja nativo</h3>
          <p class="feature-p">Cada loja com dados completamente isolados em seu próprio schema PostgreSQL. Sem mistura, sem vazamento.</p>
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
          <p>Informe o nome e pronto. Seu ambiente isolado é provisionado automaticamente — sem necessidade de configurar banco de dados ou servidor.</p>
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
              <div class="pricing-badge">Mais popular</div>
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
              @foreach ($features as $feature)
                <li>{{ $feature }}</li>
              @endforeach
            </ul>
            <a href="{{ route('register.show') }}" class="plan-cta {{ $isFeatured ? 'primary' : 'secondary' }}">Começar agora</a>
          </div>
          @php $stagger++; @endphp
        @endforeach
      </div>
    </div>
  </section>

  <!-- CTA FINAL -->
  <section class="cta-final">
    <div class="cta-final-inner">
      <h2>Sua loja pronta para operar <span>hoje.</span></h2>
      <p>Sem instalação, sem configuração. Comece o período de teste e veja a diferença.</p>
      <div class="cta-actions">
        <a href="{{ route('register.show') }}" class="btn-green-lg">Criar minha loja grátis</a>
      </div>
    </div>
  </section>

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
        <a href="{{ url('/admin') }}" style="opacity:.4">Admin</a>
      </div>
      <span class="footer-copy">© {{ date('Y') }} Zenvyra. Todos os direitos reservados.</span>
    </div>
  </footer>

  <script>
    // Navbar scroll effect
    const nav = document.getElementById('nav');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.fade-up, .fade-in').forEach(el => observer.observe(el));

    // Smooth anchor scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const id = a.getAttribute('href').slice(1);
        const el = document.getElementById(id);
        if (el) {
          e.preventDefault();
          el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  </script>
</body>
</html>
