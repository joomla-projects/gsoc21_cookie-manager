describe('Test in frontend that the users remind view', () => {
  beforeEach(() => cy.task('clearEmails'));

  it('can send a reminder email for a test user in a menu item', () => {
    cy.db_createUser({ name: 'test user', email: 'test@example.com' })
      .then(() => cy.db_createMenuItem({
        title: 'Automated test reminder', alias: 'test-reminder', path: 'test-reminder', link: 'index.php?option=com_users&view=remind',
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test reminder)').click();
        cy.get('#jform_email').type('test@example.com');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.get('#system-message-container').should('contain.text', 'If the email address you entered is registered on this site you will shortly receive an email with a reminder.');

          cy.wrap(mails).should('have.lengthOf', 1);
          cy.wrap(mails[0].body).should('have.string', 'A username reminder has been requested');
          cy.wrap(mails[0].body).should('have.string', '/test-reminder');
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].receivers).should('have.property', 'test@example.com');
        });
      });
  });

  it('can send a reminder email for a test user without a menu item', () => {
    cy.db_createUser({ name: 'test user', email: 'test@example.com' })
      .then(() => {
        cy.visit('/index.php?option=com_users&view=remind');
        cy.get('#jform_email').type('test@example.com');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.get('#system-message-container').should('contain.text', 'If the email address you entered is registered on this site you will shortly receive an email with a reminder.');

          cy.wrap(mails).should('have.lengthOf', 1);
          cy.wrap(mails[0].body).should('have.string', 'A username reminder has been requested');
          cy.wrap(mails[0].body).should('have.string', '/component/users/login');
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].receivers).should('have.property', 'test@example.com');
        });
      });
  });

  it('can not send a reminder email for a user which is not registered', () => {
    cy.visit('/index.php?option=com_users&view=remind');
    cy.get('#jform_email').type('test@example.com');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.get('#system-message-container').should('contain.text', 'If the email address you entered is registered on this site you will shortly receive an email with a reminder.');

      cy.wrap(mails).should('have.lengthOf', 0);
    });
  });
});
