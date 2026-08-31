<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Termos de Uso — Zenvyra</title>
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
    <h1>Termos de Uso</h1>
    <p class="content-meta">Última atualização: {{ date('d/m/Y') }}</p>

    <h2>1. Aceitação dos termos</h2>
    <p>Ao acessar ou utilizar a plataforma Zenvyra, você concorda com estes Termos de Uso. Se não concordar com qualquer parte destes termos, não utilize o serviço.</p>

    <h2>2. Descrição do serviço</h2>
    <p>A Zenvyra é uma plataforma SaaS de gestão de lojas que oferece recursos de catálogo de produtos, controle de estoque, gestão de pedidos e equipe de vendas. O acesso é fornecido mediante assinatura de um dos planos disponíveis.</p>

    <h2>3. Cadastro e conta</h2>
    <p>Para utilizar a plataforma, você deve fornecer informações verdadeiras e mantê-las atualizadas. Você é responsável por manter a confidencialidade de suas credenciais de acesso e por todas as atividades realizadas sob sua conta.</p>

    <h2>4. Uso aceitável</h2>
    <p>Você concorda em utilizar a plataforma somente para fins lícitos e de acordo com estes termos. É proibido:</p>
    <ul>
      <li>Usar o serviço para atividades ilegais ou fraudulentas</li>
      <li>Tentar acessar dados de outras contas ou lojas</li>
      <li>Sobrecarregar intencionalmente a infraestrutura da plataforma</li>
      <li>Revender ou sublicenciar o acesso ao serviço</li>
    </ul>

    <h2>5. Pagamento e assinatura</h2>
    <p>Os planos são cobrados mensalmente, conforme os valores vigentes na página de preços. O não pagamento resultará na suspensão do acesso. Cancelamentos entram em vigor ao final do período de faturamento corrente.</p>

    <h2>6. Dados e privacidade</h2>
    <p>O tratamento dos seus dados pessoais é regido pela nossa <a href="{{ route('legal.privacy') }}">Política de Privacidade</a>. Seus dados de loja ficam isolados em um schema dedicado e não são compartilhados com outros clientes.</p>

    <h2>7. Disponibilidade do serviço</h2>
    <p>Buscamos manter a plataforma disponível 24 horas por dia, 7 dias por semana. No entanto, podem ocorrer interrupções programadas para manutenção ou por fatores fora do nosso controle. Não garantimos disponibilidade ininterrupta.</p>

    <h2>8. Propriedade intelectual</h2>
    <p>Todos os direitos sobre a plataforma, incluindo código, design e marca, pertencem à Zenvyra. O uso do serviço não transfere nenhum direito de propriedade intelectual ao usuário.</p>

    <h2>9. Limitação de responsabilidade</h2>
    <p>A Zenvyra não se responsabiliza por danos indiretos, perda de lucros ou dados decorrentes do uso ou incapacidade de uso do serviço, exceto nos casos previstos pela legislação brasileira aplicável.</p>

    <h2>10. Alterações nos termos</h2>
    <p>Podemos atualizar estes termos periodicamente. Notificaremos usuários sobre mudanças relevantes por e-mail. O uso continuado da plataforma após as alterações constitui aceitação dos novos termos.</p>

    <h2>11. Contato</h2>
    <p>Dúvidas sobre estes termos podem ser enviadas para <a href="mailto:contato@zenvyra.com.br">contato@zenvyra.com.br</a>.</p>
  </div>

  <footer>
    <span>© {{ date('Y') }} Zenvyra. Todos os direitos reservados.</span>
  </footer>
</body>
</html>
