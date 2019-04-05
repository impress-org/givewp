/**
 * This test performs EXISTENCE tests for the shortcode [give_donor_wall]
 *
 * This is a relatively short test since there are no interactions on the page.
 */

const give = require( './test-utility' );

describe( 'Shortcode Donor Wall', () => {

	// Visit the /donor-wall page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/donor-wall/` ) )

	give.utility.fn.verifyExistence( page, [
		/**
		 * Donor image
		 */
		{
			desc: 'verify donor 1 has image',
			selector: '.give-donor__image',
			innerText: 'SH',
		},

		/**
		 * Donor Name
		 */
		{
			desc: 'verify donor name as "Ryan Howard"',
			selector: '.give-donor__name',
			innerText: 'Stanley Hudson',
		},
	])
})
