describe('Administradores - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/administrators');
  });

  it('deve exibir o cabeçalho e breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Administradores');
    cy.get('.fi-breadcrumbs-item-label').first().should('contain.text', 'Administradores');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Listar');
  });

  it('deve exibir o botão de criar administrador e navegar ao clicar', () => {
    cy.contains('a', 'Criar administrador').should('be.visible').click();
    cy.url().should('include', '/admin/administrators/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="administradores"]').should('be.visible');
    ['Nome', 'E-mail'].forEach((coluna) => {
      cy.contains('table[aria-label="administradores"] th.fi-ta-header-cell', coluna).should('be.visible');
    });
  });

  it('deve listar os administradores cadastrados', () => {
    cy.get('tbody tr.fi-ta-row').should('have.length.at.least', 1);
    cy.get('.fi-ta-cell-name').first().invoke('text').should('not.be.empty');
    cy.get('.fi-ta-cell-email').first().invoke('text').should('match', /@/);
  });

  it('deve exibir o administrador master na lista', () => {
    cy.contains('.fi-ta-cell-email', 'master@zenvyra.com.br').should('be.visible');
  });

  it('deve exibir a ação de editar em cada administrador', () => {
    cy.get('tbody tr.fi-ta-row').each(($row) => {
      cy.wrap($row).find('a.fi-ac-link-action[href*="/edit"]').should('exist');
    });
  });

  it('deve exibir o total correto de resultados na paginação', () => {
    cy.get('.fi-pagination-overview').invoke('text').then((text) => {
      const match = text.match(/de (\d+) resultados/);
      expect(match, 'texto de paginação deve conter o total').to.not.be.null;
      const total = parseInt(match[1], 10);
      const itensPagina1 = Math.min(total, 10);

      cy.get('.fi-pagination-overview').should(
        'contain.text',
        `Exibindo 1 a ${itensPagina1} de ${total} resultados`
      );
      cy.get('tbody tr.fi-ta-row').should('have.length', itensPagina1);
    });
  });

  it('deve filtrar a lista ao pesquisar pelo nome de um administrador', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Admin Master');
    // should com callback: o Cypress repete até a tabela terminar de filtrar
    cy.get('.fi-ta-cell-name').should(($cells) => {
      expect($cells.length).to.be.at.least(1);
      $cells.each((_, el) => {
        expect(el.innerText.toLowerCase()).to.contain('admin master');
      });
    });
  });

  it('deve navegar para a tela de edição ao clicar em um administrador', () => {
    cy.get('.fi-ta-cell-name a').first().click();
    cy.url().should('match', /\/admin\/administrators\/\d+\/edit/);
  });
});
