const cy = window.cy;
const baseURL = window.baseURL;

describe( 'Test onboarding wizard', function() {
	it( 'can enable setup page', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=advanced' );
		cy.get( 'input[name="setup_page_enabled"][value="enabled"]' ).click();
		cy.get( 'input[name="save"]' ).click();
		cy.visit( baseURL + '/wp-admin' );
		cy.get( 'a[href="edit.php?post_type=give_forms&page=give-setup"]' ).should( 'exist' );
	} );
	it( 'can open the onboarding wizard', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-setup' );
		cy.get( 'article[data-givewp-test="onboarding-wizard-link"]' ).click();
		cy.get( 'button[data-givewp-test="start-setup"]' ).should( 'exist' );
	} );
} );
