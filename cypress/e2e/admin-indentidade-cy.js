describe('Identidade Visual - Painel Admin', () => {
  beforeEach(() => {
    cy.session('admin-logado', () => {
      cy.visit('/admin/login');
      cy.get('input[type="email"]').type(Cypress.env('ADMIN_EMAIL'));
      cy.get('input[type="password"]').type(Cypress.env('ADMIN_PASSWORD'));
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/admin');
      cy.url().should('not.include', '/login');
    });
    cy.visit('/admin/configuracoes/identidade-visual?record=1');
  });

  it('deve exibir o título e o breadcrumb corretos', () => {
    cy.get('h1.fi-header-heading').should('contain.text', 'Identidade Visual');
    cy.get('.fi-breadcrumbs-item-label').last().should('contain.text', 'Editar');
  });

  it('deve exibir o botão de restaurar cores padrão', () => {
    cy.contains('button', 'Restaurar cores padrão').should('be.visible');
  });

  it('deve exibir as abas Geral, Modo Claro e Modo Escuro', () => {
    ['Geral', 'Modo Claro', 'Modo Escuro'].forEach((aba) => {
      cy.contains('.fi-tabs-item-label', aba).should('be.visible');
    });
  });

  it('deve exibir a seção de marca do painel com o nome do sistema preenchido', () => {
    cy.contains('.fi-section-header-heading', 'Marca do painel master').should('be.visible');
    cy.get('input[id="form.nome_sistema"]').should('be.visible').invoke('val').should('not.be.empty');
    cy.contains('.fi-fo-field-label-content', 'Nome exibido no painel').should('be.visible');
    cy.contains('.fi-fo-field-label-content', 'Logotipo').should('exist');
    cy.contains('.fi-fo-field-label-content', 'Favicon').should('exist');
  });

  it('deve exibir os campos de cores dos botões e badges', () => {
    cy.contains('.fi-section-header-heading', 'Cores dos botões e badges').should('exist');
    ['Cor primária', 'Cor de sucesso', 'Cor de alerta', 'Cor de erro'].forEach((campo) => {
      cy.contains('.fi-fo-field-label-content', campo).should('exist');
    });
  });

  it('deve alternar entre as abas ao clicar', () => {
    cy.contains('.fi-tabs-item', 'Modo Claro').click();
    cy.contains('.fi-tabs-item', 'Modo Claro').should('have.attr', 'aria-selected', 'true');
    cy.contains('.fi-tabs-item', 'Modo Escuro').click();
    cy.contains('.fi-tabs-item', 'Modo Escuro').should('have.attr', 'aria-selected', 'true');
    cy.contains('.fi-tabs-item', 'Geral').click();
    cy.contains('.fi-tabs-item', 'Geral').should('have.attr', 'aria-selected', 'true');
  });

  it('deve exibir os botões de salvar e cancelar', () => {
    cy.get('button[wire\\:target="save"]').should('exist');
    cy.contains('button', 'Cancelar').should('exist');
  });

  it('deve exibir o item Identidade Visual ativo no menu lateral', () => {
    cy.contains('.fi-sidebar-item-label', 'Identidade Visual').should('exist');
  });
});
