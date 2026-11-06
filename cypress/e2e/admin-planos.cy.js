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
    cy.get('.fi-breadcrumbs-item-label').first().should('contain.text', 'Planos');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Listar');
  });

  it('deve exibir o botão de criar plano e navegar ao clicar', () => {
    cy.contains('a', 'Criar plano').should('be.visible').click();
    cy.url().should('include', '/admin/plans/create');
  });

  it('deve exibir a tabela com as colunas corretas', () => {
    cy.get('table[aria-label="planos"]').should('be.visible');
    ['Nome', 'Slug', 'Preço', 'Período', 'Ativo'].forEach((coluna) => {
      cy.contains('table[aria-label="planos"] th.fi-ta-header-cell', coluna).should('be.visible');
    });
  });

  it('deve listar os planos cadastrados', () => {
    cy.get('tbody tr.fi-ta-row').should('have.length.at.least', 1);
    cy.get('.fi-ta-cell-name').first().invoke('text').should('not.be.empty');
    cy.get('.fi-ta-cell-slug').first().invoke('text').should('not.be.empty');
  });

  it('deve exibir o preço em formato de moeda e o período como badge', () => {
    cy.get('.fi-ta-cell-price').each(($cell) => {
      expect($cell.text()).to.match(/R\$\s*[\d.]+,\d{2}/);
    });
    cy.get('.fi-ta-cell-billing-period .fi-badge').first().should('exist');
  });

  it('deve exibir o status ativo de cada plano', () => {
    cy.get('tbody tr.fi-ta-row').each(($row) => {
      cy.wrap($row).find('.fi-ta-cell-is-active .fi-ta-icon').should('exist');
    });
  });

  it('deve exibir as ações de ativar/desativar e editar', () => {
    cy.get('tbody tr.fi-ta-row').first().within(() => {
      cy.get('button.fi-ac-link-action').invoke('text').should('match', /Ativar|Desativar/);
      cy.get('a.fi-ac-link-action[href*="/edit"]').should('exist');
    });
  });

  it('deve ordenar a lista ao clicar na coluna Preço', () => {
    cy.get('th.fi-ta-header-cell-price').should('have.attr', 'aria-sort', 'none');
    cy.get('th.fi-ta-header-cell-price .fi-ta-header-cell-sort-btn').click();
    cy.get('th.fi-ta-header-cell-price').should('have.attr', 'aria-sort', 'ascending');
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

  it('deve filtrar a lista ao pesquisar pelo nome de um plano', () => {
    cy.get('.fi-ta-cell-name').first().invoke('text').then((nome) => {
      const termo = nome.trim();
      cy.get('input[wire\\:model\\.live\\.debounce\\.500ms="tableSearch"]').type(termo);
      // should com callback: o Cypress repete até a tabela terminar de filtrar
      cy.get('.fi-ta-cell-name').should(($cells) => {
        expect($cells.length).to.be.at.least(1);
        $cells.each((_, el) => {
          expect(el.innerText.toLowerCase()).to.contain(termo.toLowerCase());
        });
      });
    });
  });

  it('deve navegar para a tela de edição ao clicar em um plano', () => {
    cy.get('.fi-ta-cell-name a').first().click();
    cy.url().should('match', /\/admin\/plans\/\d+\/edit/);
  });
});
