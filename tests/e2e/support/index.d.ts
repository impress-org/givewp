/// <reference types="cypress" />

/**
 * This extends the cy command so the IDE is aware of custom commands
 */
declare namespace Cypress {
    interface Chainable<Subject> {
        /**
         * Retrieves the element by the data-givewp-test attribute.
         * Equivalent to cy.get('[data-givewp-test="name"]')
         *
         * @param name
         *
         * @example cy.getByTest('email')
         */
        getByTest(name: string): Chainable<any>;
    }
}