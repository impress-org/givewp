const give = require( './test-utility' );

describe( 'Shortcode Donor Wall', () => {
	beforeAll( async () => {
		await page.goto( `${give.utility.vars.rootUrl}/donor-wall/` )
	})

	give.utility.fn.verifyExistence( page, [
		/**
		 * Donor image
		 */
		{
			desc: 'verify donor 1 image as "EH"',
			selector: '.give-donor__image',
			innerText: 'EH',
		},

		/**
		 * Donor Name
		 */
		{
			desc: 'verify donor name as "Erin Hannon"',
			selector: '.give-donor__name',
			innerText: 'Erin Hannon',
		},
	])
})