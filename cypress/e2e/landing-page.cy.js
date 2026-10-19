describe('Landing Page - Zenyvra', () => {
  it('deve carregar a página inicial com sucesso', () => {
    cy.visit('/');
    cy.contains('Gerencie sua loja com mais clareza').should('be.visible');
  });

  it('deve exibir o botão de login e navegar até a tela de login', () => {
    cy.visit('/');
    cy.contains('Entrar').click();
    cy.url().should('include', '/app/login');
  });

  it('deve exibir o botão de cadastro e navegar até a tela de cadastro', () => {
    cy.visit('/');
    cy.contains('Começar grátis').first().click();
    cy.url().should('include', '/cadastro');
  });
});