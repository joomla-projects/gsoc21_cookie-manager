describe('Test that the folder installer plugin', () => {
  it('is shown in extension installer', () => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=install');
    cy.get('button[role="tab"]:contains(Install from Folder)').click();

    cy.contains('legend', 'Install from Folder');
  });
});
