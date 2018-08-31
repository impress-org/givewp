const give = require( './test-utility' );

describe( 'Shortcode Donor Wall', () => {
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/donor-wall/` ) )

	give.utility.fn.verifyExistence( page, [
		/**
		 * Donor image
		 */
		{
			desc: 'verify donor 1 image as "DW"',
			selector: '.give-grid__item:nth-child(1) .give-donor__image',
			innerText: 'DW',
		},

		/**
		 * Donor Name
		 */
		{
			desc: 'verify donor name as "Devin Walker"',
			selector: '.give-grid__item:nth-child(1) .give-donor__name',
			innerText: 'Devin Walker',
		},
	])
})