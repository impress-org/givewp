const give = require( './test-utility' );

describe( 'Shortcode Give Totals', () => {

	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-totals-shortcode/` ) )

	it( 'verify give totals shortcode', async () => {
		await expect( page ).toMatchElement( '.give-percentage' )
		await expect( page ).toMatchElement( '.give-progress-bar' )
	})
})
