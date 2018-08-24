const give = require( './variables' );

describe( 'Test Form UI Elements', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( give.vars.rootUrl + '/donations/simple-donation-form/' )
	})

	// Check if the donation form title is displayed correctly as "Set Donation Form".
	it( 'should display "Simple Donation Form" text as form title', async () => {
		await expect( page ).toMatchElement( '.give-form-title', 'Simple Donation Form' )
	})

	// Check if the currency is displayed correctly as "$".
	it( 'should display currency as "$"', async () => {
		const symbol = await page.$( '.give-currency-symbol' )
		await expect( symbol ).toMatch( '$' )
	})

	// Check if the display amount is displayed correctly as "$".
	it( 'should display amount as "10.00"', async () => {
		const value = await page.evaluate( () => document.querySelector( '#give-amount' ).value )
		await expect( value ).toBe( '10.00' )
	})

	it( 'verify donation levels with custom price button', async () => {
		const buttonOne   = await page.$( '.give-btn-level-0' )
		const buttonTwo   = await page.$( '.give-btn-level-1' )
		const buttonThree = await page.$( '.give-btn-level-2' )
		const buttonFour  = await page.$( '.give-btn-level-custom' )

		await expect( buttonOne ).toMatch( 'Bronze' )
		await expect( buttonTwo ).toMatch( 'Silver' )
		await expect( buttonThree ).toMatch( 'Gold' )
		await expect( buttonFour ).toMatch( 'or donate what you like!' )
	})

	it( 'should display 3 payment methods', async () => {
		const test    = await page.$( '#give-gateway-option-manual' )
		const offline = await page.$( '#give-gateway-option-offline' )
		const paypal  = await page.$( '#give-gateway-option-paypal' )

		await expect( test ).toMatch( 'Test Donation' )
		await expect( offline ).toMatch( 'Offline Donation' )
		await expect( paypal ).toMatch( 'PayPal' )
	})

	it( 'check all fields for Personal Info', async () => {
		const mr    = await page.evaluate( () => document.querySelector( '#give-title' ).options[0].innerText )
		const mrs   = await page.evaluate( () => document.querySelector( '#give-title' ).options[1].innerText )
		const ms    = await page.evaluate( () => document.querySelector( '#give-title' ).options[2].innerText )
		const dr    = await page.evaluate( () => document.querySelector( '#give-title' ).options[3].innerText )
		const value = await page.evaluate( () => document.querySelector( '#give-purchase-button' ).value )	

		await expect( page ).toMatchElement( '#give-title' )
		await expect( page ).toMatchElement( '#give-first' )
		await expect( page ).toMatchElement( '#give-last' )
		await expect( page ).toMatchElement( '#give-company' )
		await expect( page ).toMatchElement( '#give-email' )
		await expect( page ).toMatchElement( '#give-anonymous-donation' )
		await expect( page ).toMatchElement( '#give-comment' )

		await expect( mr ).toBe( 'Mr.' )
		await expect( mrs ).toBe( 'Mrs.' )
		await expect( ms ).toBe( 'Ms.' )
		await expect( dr ).toBe( 'Dr.' )
		await expect( value ).toBe( 'Make a Donation' )
	})
})
