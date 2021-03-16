const cy = window.cy;
const baseURL = window.baseURL;

describe( 'Test Logs page', function() {
	it( 'can use logs table', function() {
		// Flush logs
		cy.exec( 'wp-env run cli wp give flush-logs' );

		// Seed logs
		cy.exec( 'wp-env run cli "wp give test-logs --type=notice --category=CypressCategory --count=5"' );
		cy.exec( 'wp-env run cli "wp give test-logs --type=error --source=CypressSource"' );

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

		// Wait for the react state to update the DOM
		cy.wait( 100 );

		// Check sorted
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).each( ( row ) => {
			cy.wrap( row.children().eq( 0 ) ).should( 'contain.text', 'Notice' );
		} );

		// Reset status sort
		cy.getByTest( 'logs-status-dropdown' ).select( 'All statuses' );

		// Sort by Category
		cy.getByTest( 'logs-category-dropdown' ).select( 'CypressCategory' );
		cy.getByTest( 'logs-category-dropdown' ).should( 'have.value', 'CypressCategory' );

		// Wait for the react state to update the DOM
		cy.wait( 100 );

		// Check sorted
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).each( ( row ) => {
			cy.wrap( row.children().eq( 1 ) ).should( 'contain.text', 'CypressCategory' );
		} );

		// Reset category sort
		cy.getByTest( 'logs-category-dropdown' ).select( 'All categories' );

		// Sort by Source
		cy.getByTest( 'logs-source-dropdown' ).select( 'CypressSource' );
		cy.getByTest( 'logs-source-dropdown' ).should( 'have.value', 'CypressSource' );

		// Wait for the react state to update the DOM
		cy.wait( 100 );

		// Check sorted
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).each( ( row ) => {
			cy.wrap( row.children().eq( 2 ) ).should( 'contain.text', 'CypressSource' );
		} );

		// Flush logs
		cy.getByTest( 'flush-logs-btn' ).click();
		cy.getByTest( 'flush-logs-confirm-btn' ).click();
		cy.getByTest( 'logs-table' ).find( '.give-table-row' ).should( 'not.exist' );
	} );
} );

