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
    cy.get('.fi-breadcrumbs-item-label').first().should('contain.text', 'Lojistas');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Listar');
  });

  it('deve exibir o botão de criar lojista e navegar ao clicar', () => {
    cy.contains('a', 'Criar lojista').should('be.visible').click();
    cy.url().should('include', '/admin/store-owners/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="lojistas"]').should('be.visible');
    ['Nome', 'E-mail', 'Lojas'].forEach((coluna) => {
      cy.contains('table[aria-label="lojistas"] th.fi-ta-header-cell', coluna).should('be.visible');
    });
  });

  it('deve listar os lojistas cadastrados', () => {
    cy.get('tbody tr.fi-ta-row').should('have.length.at.least', 1);
    cy.get('.fi-ta-cell-name').first().invoke('text').should('not.be.empty');
    cy.get('.fi-ta-cell-email').first().invoke('text').should('match', /@/);
  });

  it('deve exibir a loja vinculada a cada lojista', () => {
    cy.get('tbody tr.fi-ta-row').first()
      .find('.fi-ta-cell-tenants\\.te-name .fi-badge')
      .should('exist');
  });

  it('deve exibir as ações de tornar admin master e editar', () => {
    cy.get('tbody tr.fi-ta-row').first().within(() => {
      cy.get('button.fi-ac-link-action').invoke('text').should('match', /admin master/i);
      cy.get('a.fi-ac-link-action[href*="/edit"]').should('exist');
    });
  });

  it('deve exibir a paginação corretamente com base no total de resultados', () => {
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

  it('deve navegar para a segunda página de resultados', () => {
    cy.get('.fi-pagination-overview').invoke('text').then((text) => {
      const total = parseInt(text.match(/de (\d+) resultados/)[1], 10);

      if (total <= 10) {
        cy.log('Total de lojistas cabe em uma página; teste de segunda página ignorado');
        return;
      }

      const fimPagina2 = Math.min(total, 20);
      cy.get('[aria-label="Ir para página 2"]').first().click();
      cy.get('.fi-pagination-overview').should(
        'contain.text',
        `Exibindo 11 a ${fimPagina2} de ${total} resultados`
      );
    });
  });

  it('deve filtrar a lista ao pesquisar pelo nome de um lojista', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Lojista Demo');
    // should com callback: o Cypress repete até a tabela terminar de filtrar
    cy.get('.fi-ta-cell-name').should(($cells) => {
      expect($cells.length).to.be.at.least(1);
      $cells.each((_, el) => {
        expect(el.innerText.toLowerCase()).to.contain('lojista demo');
      });
    });
  });

  it('deve navegar para a tela de edição ao clicar em um lojista', () => {
    cy.get('.fi-ta-cell-name a').first().click();
    cy.url().should('match', /\/admin\/store-owners\/\d+\/edit/);
  });
});
