/**
 * This test performs EXISTENCE tests for the shortcode [donation_history]
 *
 * It fills the login form with the credentials as shown below in the tests.
 */

const give = require( './test-utility' );

describe( 'Shortcode Donation History', () => {

	// Visit the /give-donation-history-shortcode page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-donation-history-shortcode/` ) )

	// Fill the login form with the following credentials.
	give.utility.fn.logIn( page, {
		username: 'sam.smith@gmail.com',
		password: 'sam12345',
	})

	// Verify the output after logging in.
	it( 'EXISTENCE: verify donation history shortcode page', async () => {

		// Visit the /give-donation-history-shortcode page.
		await Promise.all([
			page.goto( `${give.utility.vars.rootUrl}/give-donation-history-shortcode/` ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } )
		])

		if ( null === await page.$( '.give-donation-details a' ) ) {

			// If there are no donations, then verify for the following output.
			await expect( page ).toMatch( `It looks like you haven't made any donations.` )
		} else {

			// If donations are found, then click the link to view details.
			await page.waitForSelector( '.give-donation-details a' )
			await Promise.all([
				page.click( '.give-donation-details a' ),
				page.waitForNavigation( { waitUntil: 'networkidle2' } )
			])
		}

	})

	// Logout of WordPress.
	give.utility.fn.logOut( page )
})
