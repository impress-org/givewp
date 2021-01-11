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
		cy.getByTest( 'setup-configuration' ).click();
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
		cy.getByTest( 'cause-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 2 ).click();
			cy.get( '.givewp-select__value-container' ).contains( 'Environmental' );
		} );

		// Cause continue button should lead to location step
		cy.getByTest( 'cause-continue-button' ).click();
		cy.getByTest( 'location-continue-button' ).should( 'exist' );

		// Changes to country should update options in state/province and currency inputs

		// Set country to Canada
		cy.getByTest( 'country-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 1 ).click();
		} );

		// Check Canadian provinces
		cy.getByTest( 'state-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 1 ).contains( 'Alberta' );
			cy.get( '.givewp-select__menu-list > div' ).eq( 2 ).contains( 'British Columbia' );
		} );

		// Check Candaian currency
		cy.getByTest( 'currency-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).contains( 'Canadian Dollars ($)' );
		} );

		// Set country to UK
		cy.getByTest( 'country-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 2 ).click();
		} );

		// Check that state select input does not exist
		cy.getByTest( 'state-select' ).should( 'not.exist' );

		// Check UK currency
		cy.getByTest( 'currency-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).contains( 'Pounds Sterling (£)' );
		} );

		// Set country to US
		cy.getByTest( 'country-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 0 ).click();
		} );

		// Check US states
		cy.getByTest( 'state-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).click();
			cy.get( '.givewp-select__menu-list > div' ).eq( 1 ).contains( 'Alabama' );
			cy.get( '.givewp-select__menu-list > div' ).eq( 2 ).contains( 'Alaska' );
		} );

		// Check US currency
		cy.getByTest( 'currency-select' ).within( () => {
			cy.get( '.givewp-select__value-container' ).contains( 'US Dollars ($)' );
		} );

		// Location continue button should lead to features step
		cy.getByTest( 'location-continue-button' ).click();
		cy.getByTest( 'features-continue-button' ).should( 'exist' );

		// Multiple feature cards can be selected
		cy.get( 'label[for="donation-comments"]' ).click();
		cy.get( 'label[for="terms-conditions"]' ).click();
		cy.get( 'input[value="donation-comments"]' ).should( 'have.attr', 'checked' );
		cy.get( 'input[value="terms-conditions"]' ).should( 'have.attr', 'checked' );

		// Feature cards can be unselected
		cy.get( 'label[for="donation-comments"]' ).click();
		cy.get( 'label[for="terms-conditions"]' ).click();
		cy.get( 'input[value="donation-comments"]' ).should( 'not.have.attr', 'checked' );
		cy.get( 'input[value="terms-conditions"]' ).should( 'not.have.attr', 'checked' );

		// Features continue button should lead to preview step
		cy.getByTest( 'features-continue-button' ).click();
		cy.getByTest( 'preview-continue-button' ).should( 'exist' );

		// Prevew form iframe should load
		cy.getByTest( 'preview-form' ).within( () => {
			cy.get( 'iframe' ).its( '0.contentDocument.body' ).should( 'be.visible' );
		} );

		// Preview continue button should lead to addons step
		cy.getByTest( 'preview-continue-button' ).click();
		cy.getByTest( 'addons-continue-button' ).should( 'exist' );

		// Multiple addon cards can be selected
		cy.get( 'label[for="recurring-donations"]' ).click();
		cy.get( 'label[for="donors-cover-fees"]' ).click();
		cy.get( 'input[value="recurring-donations"]' ).should( 'have.attr', 'checked' );
		cy.get( 'input[value="donors-cover-fees"]' ).should( 'have.attr', 'checked' );

		// Addon cards can be unselected
		cy.get( 'label[for="recurring-donations"]' ).click();
		cy.get( 'label[for="donors-cover-fees"]' ).click();
		cy.get( 'input[value="recurring-donations"]' ).should( 'not.have.attr', 'checked' );
		cy.get( 'input[value="donors-cover-fees"]' ).should( 'not.have.attr', 'checked' );

		// Top navigation should work as expected

		// Check that first step link works as expected
		cy.getByTest( 'navigation-step' ).eq( 0 ).click();
		cy.getByTest( 'cause-continue-button' ).should( 'exist' );

		// Check that third step link works as expected
		cy.getByTest( 'navigation-step' ).eq( 2 ).click();
		cy.getByTest( 'features-continue-button' ).should( 'exist' );

		// Check that last step link works as expected
		cy.getByTest( 'navigation-step' ).eq( 4 ).click();
		cy.getByTest( 'addons-continue-button' ).should( 'exist' );

		// Addons continue button should lead to setup page
		cy.getByTest( 'addons-continue-button' ).click();
		cy.getByTest( 'setup-configuration' ).should( 'exist' );
	} );
} );
