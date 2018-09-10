/**
 * This test performs EXISTENCE and INTERACTION tests on a form with
 * display option as "All Fields"
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

describe( 'Display Option: All fields', () => {

	// Visit the /donations/simple-donation-form page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/donations/simple-donation-form/` ) )

	give.utility.fn.verifyExistence( page, [

		{
			desc: 'verify form title as "Simple Donation Form"',
			selector: '.give-form-title',
			innerText: 'Simple Donation Form',
		},

		{
			desc: 'verify form content',
			selector: '.give-form-content-wrap p',
			innerText: 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and practice. The Army’s doctrine follows the mainstream of Christian belief and its articles of faith emphasise God’s saving purposes.',
		},

		{
			desc: 'verify currency symbol as "$"',
			selector: '.give-currency-symbol',
			innerText: '$',
		},

		{
			desc: 'verify currency value as "10.00"',
			selector: '.give-text-input',
			value: '10.00',
		},

		{
			desc: 'verify donation level 1 as "Bronze" with value "10.00"',
			selector: '.give-btn-level-0',
			innerText: 'Bronze',
			value: '10.00',
		},

		{
			desc: 'verify custom level as "or donate what you like!" with a value "custom"',
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
			desc: 'verify manual payment radio with value "manual"',
			selector: 'input[id^="give-gateway-manual"]',
			value: 'manual',
		},

		{
			desc: 'verify offline payment radio with value "offline"',
			selector: 'input[id^="give-gateway-offline"]',
			value: 'offline',
		},

		{
			desc: 'verify paypal payment radio with value "paypal"',
			selector: 'input[id^="give-gateway-paypal"]',
			value: 'paypal',
		},

		{
			desc: 'verify personal info title as "Personal Info"',
			selector: '#give_purchase_form_wrap legend',
			innerText: 'Personal Info',
		},

		{
			desc: 'verify first name label as "First Name"',
			selector: 'label[for="give-first"]',
			innerText: 'First Name',
		},

		{
			desc: 'verify first name input field placeholder as "First Name"',
			selector: '#give-first',
			placeholder: 'First Name',
		},

		{
			desc: 'verify last name label as "Last Name"',
			selector: 'label[for="give-last"]',
			innerText: 'Last Name',
		},

		{
			desc: 'verify last name input field placeholder as "Last Name"',
			selector: '#give-last',
			placeholder: 'Last Name',
		},

		{
			desc: 'verify company name label as "Company Name"',
			selector: 'label[for="give-company"]',
			innerText: 'Company Name',
		},

		{
			desc: 'verify company name input field placeholder as "Company Name"',
			selector: '#give-company',
			placeholder: 'Company Name',
		},

		{
			desc: 'verify email address label as "Email Address"',
			selector: 'label[for="give-email"]',
			innerText: 'Email Address',
		},

		{
			desc: 'verify email address input field placeholder as "Email Address"',
			selector: '#give-email',
			placeholder: 'Email Address',
		},

		{
			desc: 'verify anonymous donation label as "Make this an anonymous donation"',
			selector: 'label[for="give-anonymous-donation"]',
			innerText: 'Make this an anonymous donation',
		},

		{
			desc: 'verify anonymous donation checkbox',
			selector: '#give-anonymous-donation',
		},

		{
			desc: 'verify comment label as "Comment"',
			selector: 'label[for="give-comment"]',
			innerText: 'Comment',
		},

		{
			desc: 'verify comment textarea placeholder as "Leave a comment"',
			selector: '#give-comment',
			placeholder: 'Leave a comment',
		},

		{
			desc: 'verify create an account label as "Create an account"',
			selector: 'label[for^="give-create-account"]',
			innerText: 'Create an account',
		},

		{
			desc: 'verify create an account checkbox',
			selector: 'input[id^="give-create-account"]',
		},

		{
			desc: 'verify submit donation button value as "Make a Donation"',
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
	it( 'INTERACTION: verify select test donation payment method', async () => {
		await page.click( 'label[id="give-gateway-option-manual"]' )
		await page.waitFor( 2000 )
	})

	// Make a sample donation.
	give.utility.fn.makeDonation( page, {
		give_first: 'Stanley',
		give_last: 'Hudson',
		give_email: 'stanley.hudson@gmail.com',
	})

	// Verify the donation that was made above.
	give.utility.fn.verifyDonation( page, [
		'Payment Complete: Thank you for your donation.'
	])
})
