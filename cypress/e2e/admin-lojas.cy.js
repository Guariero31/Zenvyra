describe('Lojas - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/tenants');
  });

  it('deve exibir o cabeçalho e breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Lojas');
    cy.get('.fi-breadcrumbs-item-label').should('contain.text', 'Lojas');
  });

  it('deve exibir o botão de criar loja e navegar ao clicar', () => {
    cy.contains('a', 'Criar loja').should('be.visible').click();
    cy.url().should('include', '/admin/tenants/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="lojas"]').should('be.visible');
    cy.get('.fi-ta-header-cell-te-name').should('contain.text', 'Nome');
    cy.get('.fi-ta-header-cell-te-slug').should('contain.text', 'Slug');
    cy.get('.fi-ta-header-cell-plan-name').should('contain.text', 'Plano');
    cy.get('.fi-ta-header-cell-subscription-intent').should('contain.text', 'Intenção');
    cy.get('.fi-ta-header-cell-subscription-status').should('contain.text', 'Assinatura');
    cy.get('.fi-ta-header-cell-te-ativo').should('contain.text', 'Ativa');
  });

  it('deve listar as lojas cadastradas', () => {
    cy.contains('.fi-ta-cell', 'Loja Demo').should('be.visible');
    cy.contains('.fi-ta-cell', 'Moda Urbana').should('be.visible');
    cy.contains('.fi-ta-cell', 'Tech House').should('be.visible');
  });

  it('deve exibir o total correto de resultados na paginação', () => {
    cy.get('.fi-pagination-overview')
      .invoke('text')
      .then((text) => {
        const match = text.match(/de (\d+) resultados/);
        expect(match, 'texto de paginação deve conter o total').to.not.be.null;

        const total = parseInt(match[1], 10);
        const itensPagina1 = Math.min(total, 10);

        cy.get('.fi-pagination-overview').should(
          'contain.text',
          `Exibindo 1 a ${itensPagina1} de ${total} resultados`
        );
      });
  });

  it('deve filtrar a lista ao pesquisar pelo nome de uma loja', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Tech House');
    cy.contains('.fi-ta-cell', 'Tech House').should('be.visible');
    cy.contains('.fi-ta-cell', 'Loja Demo').should('not.exist');
  });

  it('deve navegar para a tela de edição ao clicar em uma loja', () => {
    cy.contains('.fi-ta-cell', 'Loja Demo').click();
    cy.url().should('include', '/admin/tenants/1/edit');
  });
});
