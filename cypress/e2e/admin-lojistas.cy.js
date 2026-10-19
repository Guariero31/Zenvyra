describe('Lojistas - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/store-owners');
  });

  it('deve exibir o cabeçalho e breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Lojistas');
    cy.get('.fi-breadcrumbs-item-label').should('contain.text', 'Lojistas');
  });

  it('deve exibir o botão de criar lojista e navegar ao clicar', () => {
    cy.contains('a', 'Criar lojista').should('be.visible').click();
    cy.url().should('include', '/admin/store-owners/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="lojistas"]').should('be.visible');
    cy.get('.fi-ta-header-cell-name').should('contain.text', 'Nome');
    cy.get('.fi-ta-header-cell-email').should('contain.text', 'E-mail');
    cy.get('.fi-ta-header-cell-tenants\\.te-name').should('contain.text', 'Lojas');
  });

  it('deve listar os lojistas cadastrados', () => {
    cy.contains('.fi-ta-cell', 'Lojista Demo').should('be.visible');
    cy.contains('.fi-ta-cell', 'Maria Vendas').should('be.visible');
    cy.contains('.fi-ta-cell', 'João Balcão').should('be.visible');
  });

  it('deve exibir a loja vinculada a cada lojista', () => {
    cy.contains('.fi-ta-cell', 'Maria Vendas')
      .parents('tr')
      .find('.fi-badge')
      .should('contain.text', 'Loja Demo');
  });

  it('deve exibir a paginação corretamente com base no total de resultados', () => {
    cy.get('.fi-pagination-overview')
      .invoke('text')
      .then((text) => {
        // Extrai o total a partir do texto, ex: "Exibindo 1 a 10 de 15 resultados"
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

  it('deve navegar para a segunda página de resultados', () => {
    cy.get('.fi-pagination-overview')
      .invoke('text')
      .then((text) => {
        const total = parseInt(text.match(/de (\d+) resultados/)[1], 10);

        // só faz sentido ir pra página 2 se houver mais de 10 resultados
        if (total > 10) {
          cy.contains('button', 'Próxima').click();
          cy.get('.fi-pagination-overview').should(
            'contain.text',
            `Exibindo 11 a ${total} de ${total} resultados`
          );
        } else {
          cy.contains('button', 'Próxima').should('be.disabled');
        }
      });
  });

  it('deve filtrar a lista ao pesquisar pelo nome de um lojista', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Carla Moda');
    cy.contains('.fi-ta-cell', 'Carla Moda').should('be.visible');
    cy.contains('.fi-ta-cell', 'Lojista Demo').should('not.exist');
  });

  it('deve navegar para a tela de edição ao clicar em um lojista', () => {
    cy.contains('.fi-ta-cell', 'Lojista Demo').click();
    cy.url().should('include', '/admin/store-owners/2/edit');
  });
});