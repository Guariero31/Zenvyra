describe('Landing Page - Zenyvra', () => {
  it('deve carregar a página inicial com sucesso', () => {
    cy.visit('/');
    cy.get('#hero-title').should('be.visible').and('contain.text', 'Seu negócio cresce');
    cy.contains('Cuide da sua loja com mais clareza').should('be.visible');
  });

  it('deve exibir o botão de login e navegar até a tela de login', () => {
    cy.visit('/');
    // O menu mobile também tem um link "Entrar", mas fica oculto no desktop.
    cy.get('a.btn-ghost-nav').contains('Entrar').should('be.visible').click();
    cy.url().should('include', '/app/login');
  });

  it('deve exibir o botão de cadastro e navegar até a tela de cadastro', () => {
    cy.visit('/');
    cy.get('a.btn-green').contains('Começar avaliação').should('be.visible').click();
    cy.url().should('include', '/cadastro');
  });
});
