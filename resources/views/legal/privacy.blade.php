<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Política de Privacidade — Zenvyra</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --green: #B6F542; --green-mid: #5C9E0A;
      --black: #0A0A0A; --dark-2: #1C1C1A;
      --white: #FFFFFF; --gray-50: #F8F8F5;
      --gray-200: #E4E2D8; --gray-600: #6B6965; --gray-800: #2C2B28;
      --font: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
      --max-w: 760px;
    }
    body { font-family: var(--font); color: var(--gray-800); background: var(--white); -webkit-font-smoothing: antialiased; }
    a { color: var(--green-mid); text-decoration: none; }
    a:hover { text-decoration: underline; }

    .nav { background: var(--black); padding: 0 24px; }
    .nav-inner { max-width: 1160px; margin: 0 auto; height: 60px; display: flex; align-items: center; justify-content: space-between; }
    .nav-logo { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 500; color: var(--white); text-decoration: none; }
    .nav-logo-dot { width: 7px; height: 7px; background: var(--green); border-radius: 50%; }
    .nav-back { font-size: 13px; color: rgba(255,255,255,0.5); transition: color 0.2s; }
    .nav-back:hover { color: var(--white); text-decoration: none; }

    .content { max-width: var(--max-w); margin: 0 auto; padding: 64px 24px 96px; }
    .content-tag { font-size: 12px; font-weight: 500; color: var(--green-mid); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
    .content h1 { font-size: 32px; font-weight: 500; color: var(--gray-800); letter-spacing: -0.02em; margin-bottom: 8px; }
    .content-meta { font-size: 13px; color: var(--gray-600); margin-bottom: 48px; padding-bottom: 24px; border-bottom: 1px solid var(--gray-200); }
    .content h2 { font-size: 18px; font-weight: 500; color: var(--gray-800); margin: 36px 0 12px; }
    .content p { font-size: 15px; color: var(--gray-600); line-height: 1.75; margin-bottom: 16px; }
    .content ul { padding-left: 20px; margin-bottom: 16px; }
    .content ul li { font-size: 15px; color: var(--gray-600); line-height: 1.75; margin-bottom: 6px; }

    footer { background: var(--dark-2); border-top: 1px solid rgba(255,255,255,0.06); padding: 32px 24px; text-align: center; }
    footer span { font-size: 13px; color: rgba(255,255,255,0.25); }
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-inner">
      <a href="{{ route('landing') }}" class="nav-logo">
        <div class="nav-logo-dot"></div>
        Zenvyra
      </a>
      <a href="{{ route('landing') }}" class="nav-back">← Voltar</a>
    </div>
  </nav>

  <div class="content">
    <div class="content-tag">Legal</div>
    <h1>Política de Privacidade</h1>
    <p class="content-meta">Última atualização: {{ date('d/m/Y') }}</p>

    <h2>1. Dados que coletamos</h2>
    <p>Coletamos os seguintes dados para operação da plataforma:</p>
    <ul>
      <li><strong>Dados de cadastro:</strong> nome, e-mail e informações da empresa</li>
      <li><strong>Dados de uso:</strong> páginas acessadas, ações realizadas na plataforma e logs de acesso</li>
      <li><strong>Dados operacionais:</strong> produtos, pedidos, clientes e estoque inseridos por você na plataforma</li>
      <li><strong>Dados de pagamento:</strong> processados por gateway externo; não armazenamos dados de cartão</li>
    </ul>

    <h2>2. Como usamos seus dados</h2>
    <p>Seus dados são utilizados exclusivamente para:</p>
    <ul>
      <li>Operação e melhoria da plataforma</li>
      <li>Comunicações sobre sua conta, plano e funcionalidades</li>
      <li>Suporte técnico</li>
      <li>Cumprimento de obrigações legais</li>
    </ul>

    <h2>3. Isolamento de dados</h2>
    <p>Cada loja opera em um schema PostgreSQL dedicado. Seus dados operacionais (produtos, pedidos, clientes) são completamente isolados e nunca acessíveis por outros clientes da plataforma.</p>

    <h2>4. Compartilhamento de dados</h2>
    <p>Não vendemos, alugamos ou compartilhamos seus dados com terceiros para fins comerciais. Podemos compartilhar dados com prestadores de serviço essenciais à operação (hospedagem, pagamento, e-mail transacional) mediante acordos de confidencialidade.</p>

    <h2>5. Retenção de dados</h2>
    <p>Mantemos seus dados enquanto sua conta estiver ativa. Após o cancelamento, os dados são retidos por 90 dias para fins de recuperação, após os quais são permanentemente excluídos.</p>

    <h2>6. Seus direitos (LGPD)</h2>
    <p>Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018), você tem direito a:</p>
    <ul>
      <li>Confirmar a existência de tratamento dos seus dados</li>
      <li>Acessar seus dados pessoais</li>
      <li>Corrigir dados incompletos ou desatualizados</li>
      <li>Solicitar a eliminação dos seus dados</li>
      <li>Revogar o consentimento a qualquer momento</li>
    </ul>
    <p>Para exercer qualquer um desses direitos, entre em contato pelo e-mail <a href="mailto:privacidade@zenvyra.com.br">privacidade@zenvyra.com.br</a>.</p>

    <h2>7. Cookies</h2>
    <p>Utilizamos cookies estritamente necessários para o funcionamento da plataforma (sessão autenticada). Não utilizamos cookies de rastreamento ou publicidade.</p>

    <h2>8. Segurança</h2>
    <p>Adotamos medidas técnicas e organizacionais para proteger seus dados, incluindo criptografia em trânsito (HTTPS), backups regulares e controles de acesso com base em privilégio mínimo.</p>

    <h2>9. Alterações nesta política</h2>
    <p>Podemos atualizar esta política periodicamente. Notificaremos você por e-mail sobre mudanças relevantes. A data de última atualização sempre estará indicada no topo desta página.</p>

    <h2>10. Contato</h2>
    <p>Para dúvidas sobre privacidade: <a href="mailto:privacidade@zenvyra.com.br">privacidade@zenvyra.com.br</a></p>
  </div>

  <footer>
    <span>© {{ date('Y') }} Zenvyra. Todos os direitos reservados.</span>
  </footer>
</body>
</html>
