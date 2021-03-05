const cy = window.cy;
const baseURL = window.baseURL;

describe( 'Test Logs page', function() {
	it( 'can use logs table', function() {
		// Flush logs
		cy.exec( 'wp-env run cli wp give flush-logs' );

		// Seed logs
		cy.exec( 'wp-env run cli "wp give test-logs --type=notice"' );
		cy.exec( 'wp-env run cli "wp give test-logs --type=error"' );

		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=logs' );
		// Logs table is visible
		cy.getByTest( 'logs-table' ).should( 'exist' );

		// Do we have logs displayed
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).should( 'exist' );

		// View log details
		cy.getByTest( 'view-log' ).eq( 0 ).click();
		cy.getByTest( 'log-modal' ).should( 'exist' );

		// Close log details
		cy.getByTest( 'log-modal-close' ).click();
		cy.getByTest( 'log-modal' ).should( 'not.exist' );

		// Check pagination
		cy.get( '.tablenav-pages-navspan' ).last().click();
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).should( 'exist' );

		// Sort by status
		cy.getByTest( 'logs-status-dropdown' ).select( 'notice' );
		cy.getByTest( 'logs-status-dropdown' ).should( 'have.value', 'notice' );

		// Check sorted
		cy.getByTest( 'logs-table' ).find( '.give-table-row div[class*=label]' ).should( 'satisfy', ( element ) => {
			return element[ 0 ].className.indexOf( 'notice' ) !== -1;
		} );

		// Flush logs
		cy.getByTest( 'flush-logs-btn' ).click();
		cy.getByTest( 'flush-logs-confirm-btn' ).click();
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).should( 'not.exist' );
	} );
} );

