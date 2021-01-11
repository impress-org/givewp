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
		cy.getByTest( 'intro-continue-button' ).should( 'exist' );
	} );

	it( 'can dismess onboarding wizard', function() {
		cy.visit( baseURL + '/wp-admin/?page=give-onboarding-wizard' );
		cy.getByTest( 'dismiss-wizard-link' ).click();
		cy.getByTest( 'onboarding-wizard-link' ).should( 'exist' );
	} );

	it( 'can navigate through the Onboarding Wizard', function() {
		cy.visit( baseURL + '/wp-admin/?page=give-onboarding-wizard' );

		// Intro continue button should lead to cause step
		cy.getByTest( 'intro-continue-button' ).click();
		cy.getByTest( 'cause-continue-button' ).should( 'exist' );

		// Only one fundraising type card should be selected at a time
		cy.get( 'label[for="organization"]' ).click();
		cy.get( 'input[value="organization"]' ).should( 'have.attr', 'checked' );
		cy.get( 'input[value="individual"]' ).should( 'not.have.attr', 'checked' );

		// Cause types should be selectable
		cy.getByTest( 'cause-select' ).get( '.givewp-select__value-container' ).click();
		cy.getByTest( 'cause-select' ).get( '.givewp-select__menu-list > div' ).eq( 2 ).click();
		cy.getByTest( 'cause-select' ).get( '.givewp-select__value-container' ).contains( 'Environmental' );

		// Cause continue button should lead to location step
		cy.getByTest( 'cause-continue-button' ).click();
		cy.getByTest( 'location-continue-button' ).should( 'exist' );

		// Location continue button should lead to features step
		cy.getByTest( 'location-continue-button' ).click();
		cy.getByTest( 'features-continue-button' ).should( 'exist' );

		// Features continue button should lead to preview step
		cy.getByTest( 'features-continue-button' ).click();
		cy.getByTest( 'preview-continue-button' ).should( 'exist' );

		// Preview continue button should lead to addons step
		cy.getByTest( 'preview-continue-button' ).click();
		cy.getByTest( 'addons-continue-button' ).should( 'exist' );

		// Addons continue button should lead to setup page
		cy.getByTest( 'addons-continue-button' ).click();
		cy.getByTest( 'onboarding-wizard-link' ).should( 'exist' );
	} );
} );
