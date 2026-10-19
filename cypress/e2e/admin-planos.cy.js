describe('Planos - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/plans');
  });

  it('deve exibir o cabeçalho e breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Planos');
    cy.get('.fi-breadcrumbs-item-label').should('contain.text', 'Planos');
  });

  it('deve exibir o botão de criar plano e navegar ao clicar', () => {
    cy.contains('a', 'Criar plano').should('be.visible').click();
    cy.url().should('include', '/admin/plans/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="planos"]').should('be.visible');
    cy.get('.fi-ta-header-cell-name').should('contain.text', 'Nome');
    cy.get('.fi-ta-header-cell-slug').should('contain.text', 'Slug');
    cy.get('.fi-ta-header-cell-price').should('contain.text', 'Preço');
    cy.get('.fi-ta-header-cell-billing-period').should('contain.text', 'Período');
    cy.get('.fi-ta-header-cell-is-active').should('contain.text', 'Ativo');
  });

  it('deve listar os planos cadastrados', () => {
    cy.contains('.fi-ta-cell', 'Avaliação').should('be.visible');
    cy.contains('.fi-ta-cell', 'Starter').should('be.visible');
    cy.contains('.fi-ta-cell', 'Pro').should('be.visible');
    cy.contains('.fi-ta-cell', 'Enterprise').should('be.visible');
  });

  it('deve exibir o preço e o período de cobrança de um plano', () => {
    cy.contains('.fi-ta-cell', 'Starter')
      .parents('tr')
      .within(() => {
        cy.get('.fi-ta-cell-price').should('contain.text', 'R$');
        cy.get('.fi-badge').should('contain.text', 'Mensal');
      });
  });

  it('deve exibir o status ativo/inativo de cada plano', () => {
    // "Essencial" está ativo (Sim) neste conjunto de dados
    cy.contains('.fi-ta-cell', 'Essencial')
      .parents('tr')
      .find('.fi-ta-icon')
      .should('contain.text', 'Sim');

    // "Starter" está inativo (Não)
    cy.contains('.fi-ta-cell', 'Starter')
      .parents('tr')
      .find('.fi-ta-icon')
      .should('contain.text', 'Não');
  });

  it('deve ordenar a tabela ao clicar no cabeçalho de preço', () => {
    cy.get('.fi-ta-header-cell-price button').click();
    cy.get('.fi-ta-header-cell-price').should('have.attr', 'aria-sort').and('not.eq', 'none');
  });

  it('deve exibir a paginação corretamente com base no total de resultados', () => {
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

  it('deve filtrar a lista ao pesquisar pelo nome de um plano', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Starter');
    cy.contains('.fi-ta-cell', 'Starter').should('be.visible');
    cy.contains('.fi-ta-cell', 'Enterprise').should('not.exist');
  });

  it('deve navegar para a tela de edição ao clicar em um plano', () => {
    cy.contains('.fi-ta-cell', 'Avaliação').click();
    cy.url().should('include', '/admin/plans/1/edit');
  });

  // Nota: o botão "Ativar/Desativar" (toggleStatus) altera dados reais
  // e foi propositalmente deixado fora desta suíte, seguindo a mesma
  // lógica aplicada à tela de Lojistas.
});