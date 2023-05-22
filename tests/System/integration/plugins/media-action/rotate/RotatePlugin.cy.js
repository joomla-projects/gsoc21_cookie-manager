describe('Test that the rotate media action plugin', () => {
  it('is shown when editing an image', () => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_media&view=file&mediatypes=0,1,2,3&path=local-images:/joomla_black.png');
    cy.get('button[role="tab"]:contains(Rotate)').click();

    cy.contains('legend', 'Rotate');
  });
});
