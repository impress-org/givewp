const cy = window.cy;
const baseURL = window.baseURL;

/**
 * Test interactions and functionality required to utilize the Onboarding Wizard
 */

// First, describe this spec, and what you are testing
describe( 'Test onboarding wizard', function() {
	// For reach test, describe what it aims to check
	it( 'can enable setup page', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=advanced' );

		// Target inputs by their name attribute
		cy.get( 'input[name="setup_page_enabled"][value="enabled"]' ).click();
		cy.get( 'input[name="save"]' ).click();
		cy.visit( baseURL + '/wp-admin' );

		// Be sure to include some assertion (here, that a link to the Setup Page should now exist)
		cy.get( 'a[href="edit.php?post_type=give_forms&page=give-setup"]' ).should( 'exist' );
	} );

	it( 'can open the onboarding wizard', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-setup' );

		// When not an input or anchor tag, use get by test to target elements by their data-givewp-test attribute
		cy.getByTest( 'onboarding-wizard-link' ).click();
		cy.getByTest( 'start-setup' ).should( 'exist' );
	} );
} );
