describe('Assinaturas - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/subscriptions');
  });

  it('deve exibir o cabeçalho e breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Assinaturas');
    cy.get('.fi-breadcrumbs-item-label').first().should('contain.text', 'Assinaturas');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Listar');
  });

  it('deve exibir o botão de criar assinatura e navegar ao clicar', () => {
    cy.contains('a', 'Criar assinatura').should('be.visible').click();
    cy.url().should('include', '/admin/subscriptions/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="assinaturas"]').should('be.visible');
    cy.get('th.fi-ta-header-cell-tenant\\.te-name').should('be.visible');
    ['Plano', 'Intenção', 'Status', 'Acesso liberado', 'Próxima cobrança'].forEach((coluna) => {
      cy.contains('table[aria-label="assinaturas"] th.fi-ta-header-cell', coluna).should('be.visible');
    });
  });

  it('deve listar as assinaturas cadastradas', () => {
    cy.get('tbody tr.fi-ta-row').should('have.length.at.least', 1);
    cy.get('.fi-ta-cell-tenant\\.te-name').first().invoke('text').should('not.be.empty');
    cy.get('.fi-ta-cell-plan\\.name').first().invoke('text').should('not.be.empty');
  });

  it('deve exibir badges de intenção e status em cada assinatura', () => {
    cy.get('tbody tr.fi-ta-row').each(($row) => {
      cy.wrap($row).find('.fi-ta-cell-intent .fi-badge').should('exist');
      cy.wrap($row).find('.fi-ta-cell-status .fi-badge').should('exist');
      cy.wrap($row).find('.fi-ta-cell-access .fi-ta-icon').should('exist');
    });
  });

  it('deve exibir a data da próxima cobrança no formato dd/mm/aaaa quando houver', () => {
    cy.get('.fi-ta-cell-next-billing-display').each(($cell) => {
      const texto = $cell.text().trim();
      if (/\d/.test(texto)) {
        expect(texto).to.match(/\d{2}\/\d{2}\/\d{4}/);
      }
    });
  });

  it('deve exibir as ações de editar em cada assinatura', () => {
    cy.get('tbody tr.fi-ta-row').first().within(() => {
      cy.get('a.fi-ac-link-action[href*="/edit"]').should('exist');
      cy.get('button.fi-ac-link-action').should('exist');
    });
  });

  it('deve ordenar a lista ao clicar na coluna Próxima cobrança', () => {
    cy.get('th.fi-ta-header-cell-next-billing-display').should('have.attr', 'aria-sort', 'none');
    cy.get('th.fi-ta-header-cell-next-billing-display .fi-ta-header-cell-sort-btn').click();
    cy.get('th.fi-ta-header-cell-next-billing-display').should('have.attr', 'aria-sort', 'ascending');
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

  it('deve filtrar a lista ao pesquisar pelo nome de uma loja', () => {
    cy.get('.fi-ta-cell-tenant\\.te-name').first().invoke('text').then((nome) => {
      const termo = nome.trim();
      cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type(termo);
      // should com callback: o Cypress repete até a tabela terminar de filtrar
      cy.get('.fi-ta-cell-tenant\\.te-name').should(($cells) => {
        expect($cells.length).to.be.at.least(1);
        $cells.each((_, el) => {
          expect(el.innerText.toLowerCase()).to.contain(termo.toLowerCase());
        });
      });
    });
  });

  it('deve navegar para a tela de edição ao clicar em uma assinatura', () => {
    cy.get('.fi-ta-cell-tenant\\.te-name a').first().click();
    cy.url().should('match', /\/admin\/subscriptions\/\d+\/edit/);
  });
});
