const cy = window.cy;
const baseURL = window.baseURL;

describe( 'Test setup page', function() {
	it( 'can enable setup page', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=advanced' );
		cy.get( 'input[name="setup_page_enabled"][value="enabled"]' ).click();
		cy.get( 'input[name="save"]' ).click();
		cy.visit( baseURL + '/wp-admin' );
		cy.get( 'a[href="edit.php?post_type=give_forms&page=give-setup"]' ).should( 'exist' );
	} );
	it( 'can manually launch wizard', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-setup' );
		cy.getByTest( 'setup-configuration' ).click();
		cy.getByTest( 'start-setup' ).should( 'exist' );
	})
	it( 'can dismiss setup age', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-setup' );
		cy.getByTest( 'setup-dismiss' ).click();
		cy.get( 'a[href="edit.php?post_type=give_forms&page=give-setup"]' ).should( 'not.exist' );
	})
	it( 'can disable setup page', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=advanced' );
		cy.get( 'input[name="setup_page_enabled"][value="disabled"]' ).click();
		cy.get( 'input[name="save"]' ).click();
		cy.visit( baseURL + '/wp-admin' );
		cy.get( 'a[href="edit.php?post_type=give_forms&page=give-setup"]' ).should( 'not.exist' );
	} );
} );
