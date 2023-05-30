describe('Test in backend that the category form', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'Test category'"));

  it('can create a category', () => {
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.get('#jform_title').should('exist').type('Test category');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Category saved.').should('exist');
    cy.contains('Test category');
  });

  it('check redirection to list view', () => {
    cy.visit('administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.intercept('index.php?option=com_categories&view=categories&extension=com_content').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
