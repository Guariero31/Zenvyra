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
    cy.get('.fi-breadcrumbs-item-label').first().should('contain.text', 'Lojas');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Listar');
  });

  it('deve exibir o botão de criar loja e navegar ao clicar', () => {
    cy.contains('a', 'Criar loja').should('be.visible').click();
    cy.url().should('include', '/admin/tenants/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="lojas"]').should('be.visible');
    ['Nome', 'Slug', 'Plano', 'Intenção', 'Assinatura', 'Ativa'].forEach((coluna) => {
      cy.contains('table[aria-label="lojas"] th.fi-ta-header-cell', coluna).should('be.visible');
    });
  });

  it('deve listar as lojas cadastradas', () => {
    cy.get('table[aria-label="lojas"] tbody tr.fi-ta-row').should('have.length.at.least', 1);
    cy.get('.fi-ta-cell-te-name').first().invoke('text').should('not.be.empty');
    cy.get('.fi-ta-cell-te-slug').first().invoke('text').should('not.be.empty');
  });

  it('deve exibir badges de intenção e de assinatura em cada loja', () => {
    cy.get('tbody tr.fi-ta-row').each(($row) => {
      cy.wrap($row).find('.fi-ta-cell-subscription-intent .fi-badge').should('exist');
      cy.wrap($row).find('.fi-ta-cell-subscription-status .fi-badge').should('exist');
    });
  });

  it('deve exibir as ações de ativar/desativar e editar em cada loja', () => {
    cy.get('tbody tr.fi-ta-row').first().within(() => {
      cy.get('.fi-ta-actions').should('exist');
      cy.get('a.fi-ac-link-action[href*="/edit"]').should('exist');
      cy.get('button.fi-ac-link-action').invoke('text').should('match', /Ativar|Desativar/);
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

  it('deve filtrar a lista ao pesquisar pelo nome de uma loja', () => {
    cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type('Tech House');
    // should com callback: o Cypress repete até a tabela terminar de filtrar
    cy.get('.fi-ta-cell-te-name').should(($cells) => {
      expect($cells.length).to.be.at.least(1);
      $cells.each((_, el) => {
        expect(el.innerText.toLowerCase()).to.contain('tech house');
      });
    });
  });

  it('deve navegar para a tela de edição ao clicar em uma loja', () => {
    cy.get('.fi-ta-cell-te-name a').first().click();
    cy.url().should('match', /\/admin\/tenants\/\d+\/edit/);
  });

  it('deve navegar para a tela de edição pela ação "Editar"', () => {
    cy.get('tbody tr.fi-ta-row').first().find('a.fi-ac-link-action[href*="/edit"]').click();
    cy.url().should('match', /\/admin\/tenants\/\d+\/edit/);
  });
});
