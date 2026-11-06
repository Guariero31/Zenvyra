describe('Landing Page - Zenyvra', () => {
  beforeEach(() => {
    cy.visit('/');
  });

  it('deve carregar a página inicial com sucesso', () => {
    cy.title().should('contain', 'Zenvyra');
    cy.get('h1#hero-title').should('contain.text', 'Seu negócio cresce.');
    cy.get('h1#hero-title').should('contain.text', 'gestão acompanha');
    cy.get('.hero-sub').should('contain.text', 'Cuide da sua loja com mais clareza');
    cy.get('.hero-reassurance').should('contain.text', 'Sem cartão de crédito');
  });

  it('deve exibir o menu de navegação com os links de seção', () => {
    cy.get('.nav-links').within(() => {
      cy.contains('a', 'Recursos').should('be.visible');
      cy.contains('a', 'Como funciona').should('be.visible');
      cy.contains('a', 'Planos').should('be.visible');
    });
  });

  it('deve exibir o botão de login e navegar até a tela de login', () => {
    cy.get('.nav-cta .btn-ghost-nav').should('be.visible').and('contain.text', 'Entrar').click();
    cy.url().should('include', '/app/login');
  });

  it('deve exibir o botão de cadastro e navegar até a tela de cadastro', () => {
    cy.get('.nav-cta .btn-green').should('be.visible').and('contain.text', 'Começar avaliação').click();
    cy.url().should('include', '/cadastro');
  });

  it('deve navegar ao cadastro pelo botão principal do hero', () => {
    cy.contains('.hero-actions a', 'Experimentar a Zenvyra').should('be.visible').click();
    cy.url().should('include', '/cadastro');
  });

  it('deve exibir a faixa de benefícios principais', () => {
    cy.get('.benefit-band').within(() => {
      cy.contains('h2', 'Catálogo online').should('exist');
      cy.contains('h2', 'Estoque sob controle').should('exist');
      cy.contains('h2', 'Vendas organizadas').should('exist');
    });
  });

  it('deve exibir a seção de funcionalidades com 6 cards', () => {
    cy.get('#funcionalidades').scrollIntoView();
    cy.get('#funcionalidades .feature-card').should('have.length', 6);
    [
      'Catálogo organizado',
      'Estoque sob controle',
      'Pedidos do início ao fim',
      'Clientes em foco',
      'Equipe de vendas',
    ].forEach((titulo) => {
      cy.contains('#funcionalidades .feature-h3', titulo).should('exist');
    });
  });

  it('deve exibir a seção "Como funciona" com 3 passos', () => {
    cy.get('#como-funciona .step').should('have.length', 3);
    cy.contains('#como-funciona h3', 'Crie sua loja').should('exist');
    cy.contains('#como-funciona h3', 'Monte seu catálogo').should('exist');
    cy.contains('#como-funciona h3', 'Comece a vender').should('exist');
  });

  it('deve exibir os planos com preço e botão de cadastro', () => {
    cy.get('#precos .pricing-card').should('have.length.at.least', 1);
    cy.get('#precos .pricing-card').each(($card) => {
      cy.wrap($card).find('.plan-name').invoke('text').should('not.be.empty');
      cy.wrap($card).find('.plan-price').invoke('text').should('match', /R\$\s*[\d.]+,\d{2}/);
      cy.wrap($card).find('a.plan-cta').should('have.attr', 'href').and('include', '/cadastro?plano=');
    });
  });

  it('deve destacar um plano como recomendado', () => {
    cy.get('#precos .pricing-card.featured').should('have.length', 1);
    cy.get('#precos .pricing-card.featured .pricing-badge').should('exist');
  });

  it('deve navegar ao cadastro com o plano escolhido', () => {
    cy.get('#precos .pricing-card a.plan-cta').first().invoke('attr', 'href').then((href) => {
      const plano = href.split('plano=')[1];
      cy.get('#precos .pricing-card a.plan-cta').first().scrollIntoView().click();
      cy.url().should('include', '/cadastro');
      cy.url().should('include', `plano=${plano}`);
    });
  });

  it('deve exibir as perguntas frequentes e abrir uma resposta', () => {
    cy.get('#duvidas details').should('have.length.at.least', 1);
    cy.contains('#duvidas summary', 'Preciso instalar algum programa?').scrollIntoView().click();
    cy.contains('#duvidas details', 'Preciso instalar algum programa?').should('have.attr', 'open');
    cy.contains('#duvidas details', 'Preciso instalar algum programa?').should('contain.text', 'navegador');
  });

  it('deve exibir a chamada final para o cadastro', () => {
    cy.get('.cta-final').scrollIntoView();
    cy.contains('.cta-final h2', 'Sua loja pronta para operar').should('be.visible');
    cy.contains('.cta-final a', 'Começar minha avaliação').should('have.attr', 'href').and('include', '/cadastro');
  });

  it('deve exibir o rodapé com os links institucionais', () => {
    cy.get('footer').scrollIntoView();
    cy.contains('footer a', 'Termos de uso').should('have.attr', 'href').and('include', '/termos');
    cy.contains('footer a', 'Privacidade').should('have.attr', 'href').and('include', '/privacidade');
    cy.contains('footer a', 'Contato').should('have.attr', 'href').and('include', 'mailto:');
    cy.get('footer').should('contain.text', 'Todos os direitos reservados');
  });
});
