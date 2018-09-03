const give = require( './test-utility' );

describe( 'Shortcode Donation History', () => {

	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-donation-history-shortcode/` ) )

	give.utility.fn.logIn( page, {
		username: 'sam.smith@gmail.com',
		password: 'sam12345',
	})

	it( 'EXISTENCE: verify donation history shortcode page', async () => {

		await Promise.all([
			page.goto( `${give.utility.vars.rootUrl}/give-donation-history-shortcode/` ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } )
		])

		if ( null === await page.$( '.give-donation-details a' ) ) {
			await expect( page ).toMatch( `It looks like you haven't made any donations.` )
		} else {
			await page.waitForSelector( '.give-donation-details a' )
			await Promise.all([
				page.click( '.give-donation-details a' ),
				page.waitForNavigation( { waitUntil: 'networkidle2' } )
			])
		}

	})

	give.utility.fn.logOut( page )
})
