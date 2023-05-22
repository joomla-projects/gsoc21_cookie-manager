describe('Test in backend that the module list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_modules&view=modules&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Modules');
  });

  it('can display a list of modules', () => {
    cy.db_createModule({ title: 'Test module', module: 'mod_custom' }).then(() => {
      cy.reload();

      cy.contains('Test module');
    });
  });

  it('can open the module list', () => {
    cy.clickToolbarButton('New');

    cy.contains('Select a Module Type');
  });

  it('can publish the test module', () => {
    cy.db_createModule({ title: 'Test module', module: 'mod_custom', published: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test module');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Module published').should('exist');
    });
  });

  it('can unpublish the test module', () => {
    cy.db_createModule({ title: 'Test module', module: 'mod_custom', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test module');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Module unpublished').should('exist');
    });
  });

  it('can trash the test module', () => {
    cy.db_createModule({ title: 'Test module', module: 'mod_custom' }).then(() => {
      cy.reload();
      cy.searchForItem('Test module');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Module trashed').should('exist');
    });
  });

  it('can delete the test module', () => {
    cy.db_createModule({ title: 'Test module', module: 'mod_custom', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('state', 'Trashed');
      cy.searchForItem('Test module');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Module deleted').should('exist');
    });
  });
});
