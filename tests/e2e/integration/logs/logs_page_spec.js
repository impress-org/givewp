const cy = window.cy;
const baseURL = window.baseURL;

describe( 'Test Logs page', function() {
	before( () => {
		cy.exec( 'wp-env run cli "wp give test-logs --type=notice"' );
		cy.exec( 'wp-env run cli "wp give test-logs --type=error"' );
	} );

	it( 'can use logs table', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=logs' );
		// Logs table is visible
		cy.getByTest( 'logs-table' ).should( 'exist' );

		// View log details
		cy.getByTest( 'view-log' ).eq( 0 ).click();
		cy.getByTest( 'log-modal' ).should( 'exist' );

		// Close log details
		cy.getByTest( 'log-modal-close' ).click();
		cy.getByTest( 'log-modal' ).should( 'not.exist' );

		// Sort by status
		cy.getByTest( 'logs-status-dropdown' ).select( 'notice' );
		cy.getByTest( 'logs-status-dropdown' ).should( 'have.value', 'notice' );

		// Check sorted
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).should( 'have.length', 10 );
	} );
} );

