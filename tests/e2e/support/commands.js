// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

const cy = window.cy;
const Cypress = window.Cypress;

window.baseURL = Cypress.env( 'site' ).url;
const baseURL = window.baseURL;

Cypress.Commands.add( 'getByTest', name => cy.get( `[data-givewp-test="${ name }"]` ) );

beforeEach( function() {
	cy.visit( baseURL + '/wp-login.php' );
	cy.wait( 1000 );
	cy.get( '#user_login' ).type( Cypress.env( 'wp_user' ) );
	cy.get( '#user_pass' ).type( Cypress.env( 'wp_pass' ) );
	cy.get( '#wp-submit' ).click();
} );
