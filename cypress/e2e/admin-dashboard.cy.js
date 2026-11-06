describe('Dashboard - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin');
  });

  it('deve exibir o título e o subtítulo do dashboard', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Sua plataforma, em perspectiva.');
    cy.get('.fi-header-subheading').should('contain.text', 'Visão geral das lojas e assinaturas.');
  });

  it('deve exibir o botão de criar loja e navegar ao clicar', () => {
    cy.contains('a', 'Criar loja').should('be.visible').click();
    cy.url().should('include', '/admin/tenants/create');
  });

  it('deve exibir os indicadores principais da plataforma', () => {
    const indicadores = [
      'Lojas com acesso',
      'Receita recebida',
      'Receita prevista',
      'Assinaturas em atraso',
    ];

    indicadores.forEach((label) => {
      cy.contains('.fi-wi-stats-overview-stat-label', label).should('be.visible');
    });
  });

  it('deve exibir a receita prevista em formato de moeda (ou traço, se não houver)', () => {
    cy.contains('.fi-wi-stats-overview-stat', 'Receita prevista')
      .find('.fi-wi-stats-overview-stat-value')
      .invoke('text')
      .should('match', /R\$[\s\u00a0]*[\d.,]+|—/);
  });

  it('deve exibir o aviso de situação das assinaturas', () => {
    cy.get('.panel-notice').should('be.visible');
  });

  it('deve exibir a tabela de lojas e assinaturas com as colunas corretas', () => {
    cy.get('table[aria-label="Lojas e assinaturas"]').should('be.visible');
    cy.contains('table[aria-label="Lojas e assinaturas"] th', 'Loja').should('be.visible');
    cy.contains('table[aria-label="Lojas e assinaturas"] th', 'Plano').should('be.visible');
    cy.contains('table[aria-label="Lojas e assinaturas"] th', 'Situação').should('be.visible');
    cy.contains('table[aria-label="Lojas e assinaturas"] th', 'Próximo vencimento').should('be.visible');
  });

  it('deve listar ao menos uma loja na tabela de lojas e assinaturas', () => {
    cy.get('table[aria-label="Lojas e assinaturas"] tbody tr').should('have.length.at.least', 1);
  });

  it('deve navegar para a lista de lojas ao clicar em "Ver todas"', () => {
    cy.contains('a', 'Ver todas').click();
    cy.url().should('include', '/admin/tenants');
  });

  it('deve exibir a agenda da plataforma com as ações rápidas', () => {
    cy.contains('.fi-section-header-heading', 'Agenda da plataforma').should('be.visible');
    cy.get('.panel-agenda').should('contain.text', 'vencimentos');
    cy.get('.panel-agenda').should('contain.text', 'avaliações');
    cy.contains('.panel-agenda a', 'Revisar assinaturas').should('be.visible');
    cy.contains('.panel-agenda a', 'Gerenciar planos').should('be.visible');
  });

  it('deve navegar para planos ao clicar em "Gerenciar planos"', () => {
    cy.contains('.panel-agenda a', 'Gerenciar planos').click();
    cy.url().should('include', '/admin/plans');
  });

  it('deve exibir o gráfico de crescimento de lojas', () => {
    cy.contains('.fi-section-header-heading', 'Crescimento de lojas').should('be.visible');
    cy.get('canvas[aria-label="Crescimento de lojas"]').should('exist');
  });

  it('deve exibir o resumo de assinaturas por situação', () => {
    cy.contains('.fi-section-header-heading', 'Assinaturas por situação').should('be.visible');
    cy.contains('.fi-section-header-description', /Total de \d+ assinaturas? atuais?/).should('be.visible');

    ['Em teste', 'Ativa', 'Atrasada', 'Cancelada', 'Expirada'].forEach((situacao) => {
      cy.contains('.panel-breakdown-row', situacao).should('exist');
    });
  });

  it('deve exibir os itens principais do menu lateral', () => {
    ['Visão geral', 'Lojas', 'Lojistas', 'Planos', 'Assinaturas', 'Administradores', 'Identidade Visual'].forEach(
      (item) => {
        cy.contains('.fi-sidebar-item-label', item).should('exist');
      }
    );
  });

  it('deve navegar para lojistas pelo menu lateral', () => {
    cy.get('.fi-sidebar-nav').contains('a', 'Lojistas').click();
    cy.url().should('include', '/admin/store-owners');
  });
});
