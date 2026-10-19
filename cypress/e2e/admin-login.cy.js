describe('Login - Painel Admin', () => {
  beforeEach(() => {
    cy.visit('/admin/login');
  });

  it('deve carregar a tela de login do admin', () => {
    cy.get('input[type="email"]').should('be.visible');
    cy.get('input[type="password"]').should('be.visible');
  });

  it('deve fazer login com credenciais válidas e acessar o painel', () => {
    cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
    cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/admin');
    cy.url().should('not.include', '/login');
  });

  it('deve exibir erro com credenciais inválidas', () => {
    cy.get('input[type="email"]').type('email-invalido@teste.com');
    cy.get('input[type="password"]').type('senhaerrada');
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/login');
  });
});