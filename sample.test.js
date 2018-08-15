describe( 'Set Donation Form', () => {
	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( 'http://localhost:8004/donations/set-donation-form/' )
	})

	// Check if the donation form title is displayed correctly as "Set Donation Form".
	it( 'should display "Set Donation Form" text as form title', async () => {
		await expect( page ).toMatchElement( '.give-form-title', 'Set Donation Form' )
	})

	// Check if the currency is displayed correctly as "$".
	it( 'should display currency as "$"', async () => {
		const symbol = await page.$( '.give-currency-symbol' );
		await expect( symbol ).toMatch( '$' )
	})

	// Check if the display amount is displayed correctly as "$".
	it( 'should display amount as "23.47"', async () => {
		const amount = await page.$( '#give-amount-text' );
		await expect( amount ).toMatch( '23.47' )
	})

	// Submit the donation form.
	it( 'submit the donation form', async () => {

		// Fill the form fields.
		await expect( page ).toFillForm( '.give-form', {
			give_first: 'Devin',
			give_last: 'Walker',
			give_email: 'devin.walker@gmail.com'
		})

		// Click the submit button.
		await expect( page ).toClick( '#give-purchase-button' )

		await page.waitForNavigation();
	}, 50000)

	// Verify output on the Donation Receipt page.
	it( 'verify donation receipt details', async () => {

		await expect( page ).toMatch( 'Donation Receipt' )

		await expect( page ).toMatch( 'Devin Walker' )

		await expect( page ).toMatch( '$23.47' )

		await expect( page ).toMatch( 'Set Donation Form' )

	}, 50000)
})
