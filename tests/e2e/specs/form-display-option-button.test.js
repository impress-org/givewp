/**
 * This test performs EXISTENCE and INTERACTION tests on a form with
 * display option as "Button"
 *
 * For EXISTENCE tests, it tests for
 * - Donation Title
 * - Donation Form Content
 * - Currency Symbol
 * - Currency Value
 * - One of the levels of the multi-level form
 * - Custom level
 * - Form legends
 * - All input labels and fields
 *
 * For INTERACTION tests, it tests for
 * - hover on all the tooltips present in the form
 * - clicks through all payment methods
 *
 * Makes a sample donation using the 'Test Donation' method.
 *
 * Verifies the donation confirmation page to test whether the donation was successful
 */
const give = require( './test-utility' );

describe( 'Display option: Button', () => {

	// Visit the /donations/button-form page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/donations/button-form/` ) )

	// This will reveal the form.
	it( 'INTERACTION: click donate button to reveal the form', async () => {

		// Click the button to enter custom donation amount.
		await page.click( '.give-btn-level-custom' )

		// Wait for custom amount input field to load.
		await page.waitForSelector( '.give-btn-level-custom' )

		// Fill custom amount input field with value '23.54'
		await expect( page ).toFill( 'input[name="give-amount"]', '23.54' )

		// Popup the form.
		await page.click( '.give-btn-reveal' )

		// Select the payment method.
		await page.click( 'label[id="give-gateway-option-manual"]' )
	})

	give.utility.fn.verifyExistence( page, [

		{
			desc: 'verify form title as "Button Form"',
			selector: '.give-form-title',
			innerText: 'Button Form',
		},

		{
			desc: 'verify form content as "Form Content of the Button Form."',
			selector: '.give-form-content-wrap p',
			innerText: 'Form Content of the Button Form.',
		},

		{
			desc: 'verify currency symbol as "$"',
			selector: '.give-currency-symbol',
			innerText: '$',
		},

		{
			desc: 'verify currency value "10.00"',
			selector: '.give-text-input',
			value: '23.54',
		},

		{
			desc: 'verify donation level 1 as "10.00"',
			selector: '.give-btn-level-0',
			innerText: 'Bronze',
			value: '10.00',
		},

		{
			desc: 'verify custom level as "custom"',
			selector: '.give-btn-level-custom',
			innerText: 'or donate what you like!',
			value: 'custom',
		},

		{
			desc: 'verify select payment method label as "Select Payment Method"',
			selector: '.give-payment-mode-label',
			innerText: 'Select Payment Method',
		},

		{
			desc: 'verify test donation label as "Test Donation"',
			selector: '#give-gateway-option-manual',
			innerText: 'Test Donation',
		},

		{
			desc: 'verify offline donation label as "Offline Donation"',
			selector: '#give-gateway-option-offline',
			innerText: 'Offline Donation',
		},

		{
			desc: 'verify paypal donation label as "PayPal"',
			selector: '#give-gateway-option-paypal',
			innerText: 'PayPal',
		},

		{
			desc: 'verify manual payment radio with value as "manual"',
			selector: 'input[id^="give-gateway-manual"]',
			value: 'manual',
		},

		{
			desc: 'verify offline payment radio with value as "offline"',
			selector: 'input[id^="give-gateway-offline"]',
			value: 'offline',
		},

		{
			desc: 'verify paypal payment radio with value as "paypal"',
			selector: 'input[id^="give-gateway-paypal"]',
			value: 'paypal',
		},

		{
			desc: 'verify personal info title as "Personal Info"',
			selector: '#give_purchase_form_wrap legend',
			innerText: 'Personal Info',
		},

		{
			desc: 'verify first name label',
			selector: 'label[for="give-first"]',
			innerText: 'First Name',
		},

		{
			desc: 'verify first name label input field',
			selector: '#give-first',
			placeholder: 'First Name',
		},

		{
			desc: 'verify last name label',
			selector: 'label[for="give-last"]',
			innerText: 'Last Name',
		},

		{
			desc: 'verify last name input field',
			selector: '#give-last',
			placeholder: 'Last Name',
		},

		{
			desc: 'verify company name label',
			selector: 'label[for="give-company"]',
			innerText: 'Company Name',
		},

		{
			desc: 'verify company name input field',
			selector: '#give-company',
			placeholder: 'Company Name',
		},

		{
			desc: 'verify email address label',
			selector: 'label[for="give-email"]',
			innerText: 'Email Address',
		},

		{
			desc: 'verify email address input field',
			selector: '#give-email',
			placeholder: 'Email Address',
		},

		{
			desc: 'verify anonymous donation label',
			selector: 'label[for="give-anonymous-donation"]',
			innerText: 'Make this an anonymous donation',
		},

		{
			desc: 'verify anonymous donation checkbox',
			selector: '#give-anonymous-donation',
		},

		{
			desc: 'verify comment label',
			selector: 'label[for="give-comment"]',
			innerText: 'Comment',
		},

		{
			desc: 'verify comment textarea',
			selector: '#give-comment',
			placeholder: 'Leave a comment',
		},

		{
			desc: 'verify create an account label',
			selector: 'label[for^="give-create-account"]',
			innerText: 'Create an account',
		},

		{
			desc: 'verify create an account checkbox',
			selector: 'input[id^="give-create-account"]',
		},

		{
			desc: 'verify submit donation button',
			selector: '#give-purchase-button',
			value: 'Make a Donation',
		},
	])

	give.utility.fn.verifyInteraction( page, [
		{
			desc: 'verify hover on title tooltip',
			selector: 'label[for="give-title"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on first name tooltip',
			selector: 'label[for="give-first"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on last name tooltip',
			selector: 'label[for="give-last"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on company tooltip',
			selector: 'label[for="give-company"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on email tooltip',
			selector: 'label[for="give-email"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on comment tooltip',
			selector: 'label[for="give-comment"] .give-tooltip',
			event: 'hover',
		},

		{
			desc: 'verify hover on create account tooltip',
			selector: 'label[for^="give-create-account"] .give-tooltip',
			event: 'hover',
		},
	])

	// Click the radio button related to Offline Payment Method.
	it( 'INTERACTION: verify select offline payment method', async () => {
		await page.click( 'label[id="give-gateway-option-offline"]' )
	})

	// Verify the content after clicking the offline payment button radio.
	it( 'EXISTENCE: verify offline payment method output', async () => {
		await expect( page ).toMatch( 'Make a check payable to "Give Automation"' )
	})

	// Click the radio button related to PayPal Payment Method.
	it( 'INTERACTION: verify select paypal payment method', async () => {
		await page.click( 'label[id="give-gateway-option-paypal"]' )
	})

	// Verify the content after clicking the PayPal payment button radio.
	it( 'EXISTENCE: verify paypal payment method output', async () => {
		await expect( page ).toMatch( 'Billing Details' )
	})

	/**
	 * Click the radio button related to Test Donation Payment Method.
	 * This method will be used to test a donation ahead.
	 */
	it( 'INTERACTION: verify select manual payment method', async () => {
		await page.click( 'label[id="give-gateway-option-manual"]' )
		await page.waitFor( 2000 )
	})

	// Make a sample donation.
	give.utility.fn.makeDonation( page, {
		give_first: 'Erin',
		give_last: 'Hannon',
		give_email: 'erin.hannon@gmail.com',
	})

	// Verify the donation that was made above.
	give.utility.fn.verifyDonation( page, [
		'Payment Complete: Thank you for your donation.'
	])
})
