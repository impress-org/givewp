const cy = window.cy;
const baseURL = window.baseURL;

describe( 'View reports', function() {
	it( 'can view reports', function() {
		cy.visit( baseURL + '/wp-admin/edit.php?post_type=give_forms&page=give-reports#/' );
	} );
} );
