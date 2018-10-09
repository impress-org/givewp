/**
 * This test performs EXISTENCE tests on the shortcode [give_totals]
 *
 * This test will check if the percentage and the progress bar
 * are present on the page.
 */

const give = require( './test-utility' );

describe( 'Shortcode Give Totals', () => {

	// Visit the /give-totals-shortcode page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-totals-shortcode/` ) )

	it( 'verify give totals shortcode', async () => {
		await expect( page ).toMatchElement( '.give-percentage' )
		await expect( page ).toMatchElement( '.give-progress-bar' )
	})
})
