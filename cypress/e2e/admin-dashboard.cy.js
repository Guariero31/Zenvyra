describe.skip('Dashboard - Painel Admin', () => {
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

  it('deve exibir o cabeçalho correto do dashboard', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Dashboard Administrativo');
    cy.get('p.fi-header-subheading').should('contain.text', 'Visão geral do ecossistema Zenvyra');
  });

  it('deve exibir o menu lateral com as seções e grupos principais', () => {
    cy.get('.fi-sidebar-group-label').should('contain.text', 'SaaS');
    cy.get('.fi-sidebar-group-label').should('contain.text', 'Configurações');
    cy.get('.fi-sidebar-item-label').contains('Lojas').scrollIntoView().should('be.visible');
    cy.get('.fi-sidebar-item-label').contains('Lojistas').scrollIntoView().should('be.visible');
    cy.get('.fi-sidebar-item-label').contains('Planos').scrollIntoView().should('be.visible');
    cy.get('.fi-sidebar-item-label').contains('Assinaturas').scrollIntoView().should('be.visible');
    cy.get('.fi-sidebar-item-label').contains('Administradores').scrollIntoView().should('be.visible');
  });

  it('deve exibir os cards de indicadores com valores', () => {
    cy.contains('.zv-label', 'Total de Lojas').parent().find('.zv-value').should('be.visible');
    cy.contains('.zv-label', 'Lojas Ativas').parent().find('.zv-value').should('be.visible');
    cy.contains('.zv-label', 'Faturamento Mensal').parent().find('.zv-value').should('be.visible');
  });

  it('deve navegar para a tela de Lojas ao clicar no menu', () => {
    cy.get('.fi-sidebar-item-label').contains('Lojas').scrollIntoView().click();
    cy.url().should('include', '/admin/tenants');
  });

  it('deve conseguir fazer logout', () => {
    cy.get('.fi-user-menu-trigger').click();
    cy.contains('button', 'Sair').should('be.visible').click();
    cy.url().should('include', '/admin/login');
  });
});
