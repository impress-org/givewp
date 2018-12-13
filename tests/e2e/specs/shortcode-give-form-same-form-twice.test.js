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
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-donation-form-shortcode-with-same-forms-twice/` ) )

	give.utility.fn.verifyExistence( page, [

		{
			desc: 'verify form title as "Simple Donation Form"',
			selector: '.give-display-onpage .give-form-title',
			innerText: 'Simple Donation Form',
		},

		{
			desc: 'verify form content',
			selector: '.give-display-onpage .give-form-content-wrap p',
			innerText: 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and practice. The Army’s doctrine follows the mainstream of Christian belief and its articles of faith emphasise God’s saving purposes.',
		},

		{
			desc: 'verify currency symbol as "$"',
			selector: '.give-display-onpage .give-currency-symbol',
			innerText: '$',
		},

		{
			desc: 'verify currency value as "10.00"',
			selector: '.give-display-onpage .give-text-input',
			value: '10.00',
		},

		{
			desc: 'verify donation level 1 as "Bronze" with value "10.00"',
			selector: '.give-display-onpage .give-btn-level-0',
			innerText: 'Bronze',
			value: '10.00',
		},

		{
			desc: 'verify custom level as "or donate what you like!" with a value "custom"',
			selector: '.give-display-onpage .give-btn-level-custom',
			innerText: 'or donate what you like!',
			value: 'custom',
		},

		{
			desc: 'verify select payment method label as "Select Payment Method"',
			selector: '.give-display-onpage .give-payment-mode-label',
			innerText: 'Select Payment Method',
		},

		{
			desc: 'verify test donation label as "Test Donation"',
			selector: '.give-display-onpage #give-gateway-option-manual',
			innerText: 'Test Donation',
		},

		{
			desc: 'verify offline donation label as "Offline Donation"',
			selector: '.give-display-onpage #give-gateway-option-offline',
			innerText: 'Offline Donation',
		},

		{
			desc: 'verify paypal donation label as "PayPal"',
			selector: '.give-display-onpage #give-gateway-option-paypal',
			innerText: 'PayPal',
		},

		{
			desc: 'verify manual payment radio with value "manual"',
			selector: '.give-display-onpage input[id^="give-gateway-manual"]',
			value: 'manual',
		},

		{
			desc: 'verify offline payment radio with value "offline"',
			selector: '.give-display-onpage input[id^="give-gateway-offline"]',
			value: 'offline',
		},

		{
			desc: 'verify paypal payment radio with value "paypal"',
			selector: '.give-display-onpage input[id^="give-gateway-paypal"]',
			value: 'paypal',
		},

		{
			desc: 'verify personal info title as "Personal Info"',
			selector: '.give-display-onpage #give_purchase_form_wrap legend',
			innerText: 'Personal Info',
		},

		{
			desc: 'verify first name label as "First Name"',
			selector: '.give-display-onpage label[for="give-first"]',
			innerText: 'First Name',
		},

		{
			desc: 'verify first name input field placeholder as "First Name"',
			selector: '.give-display-onpage #give-first',
			placeholder: 'First Name',
		},

		{
			desc: 'verify last name label as "Last Name"',
			selector: '.give-display-onpage label[for="give-last"]',
			innerText: 'Last Name',
		},

		{
			desc: 'verify last name input field placeholder as "Last Name"',
			selector: '.give-display-onpage #give-last',
			placeholder: 'Last Name',
		},

		{
			desc: 'verify company name label as "Company Name"',
			selector: '.give-display-onpage label[for="give-company"]',
			innerText: 'Company Name',
		},

		{
			desc: 'verify company name input field placeholder as "Company Name"',
			selector: '.give-display-onpage #give-company',
			placeholder: 'Company Name',
		},

		{
			desc: 'verify email address label as "Email Address"',
			selector: '.give-display-onpage label[for="give-email"]',
			innerText: 'Email Address',
		},

		{
			desc: 'verify email address input field placeholder as "Email Address"',
			selector: '.give-display-onpage #give-email',
			placeholder: 'Email Address',
		},

		{
			desc: 'verify anonymous donation label as "Make this an anonymous donation"',
			selector: '.give-display-onpage label[for="give-anonymous-donation"]',
			innerText: 'Make this an anonymous donation',
		},

		{
			desc: 'verify anonymous donation checkbox',
			selector: '.give-display-onpage #give-anonymous-donation',
		},

		{
			desc: 'verify comment label as "Comment"',
			selector: '.give-display-onpage label[for="give-comment"]',
			innerText: 'Comment',
		},

		{
			desc: 'verify comment textarea placeholder as "Leave a comment"',
			selector: '.give-display-onpage #give-comment',
			placeholder: 'Leave a comment',
		},

		{
			desc: 'verify create an account label as "Create an account"',
			selector: '.give-display-onpage label[for^="give-create-account"]',
			innerText: 'Create an account',
		},

		{
			desc: 'verify create an account checkbox',
			selector: '.give-display-onpage input[id^="give-create-account"]',
		},

		{
			desc: 'verify submit donation button value as "Make a Donation"',
			selector: '.give-display-onpage #give-purchase-button',
			value: 'Make a Donation',
		},
	])

	it( 'INTERACTION: click button to reveal the 2nd form', async () => {
		await page.click( '.give-btn-reveal' )
	})

	give.utility.fn.verifyExistence( page, [

		{
			desc: 'verify form title as "Simple Donation Form"',
			selector: '.give-display-reveal .give-form-title',
			innerText: 'Simple Donation Form',
		},

		{
			desc: 'verify form content',
			selector: '.give-display-reveal .give-form-content-wrap p',
			innerText: 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and practice. The Army’s doctrine follows the mainstream of Christian belief and its articles of faith emphasise God’s saving purposes.',
		},

		{
			desc: 'verify currency symbol as "$"',
			selector: '.give-display-reveal .give-currency-symbol',
			innerText: '$',
		},

		{
			desc: 'verify currency value as "10.00"',
			selector: '.give-display-reveal .give-text-input',
			value: '10.00',
		},

		{
			desc: 'verify donation level 1 as "Bronze" with value "10.00"',
			selector: '.give-display-reveal .give-btn-level-0',
			innerText: 'Bronze',
			value: '10.00',
		},

		{
			desc: 'verify custom level as "or donate what you like!" with a value "custom"',
			selector: '.give-display-reveal .give-btn-level-custom',
			innerText: 'or donate what you like!',
			value: 'custom',
		},

		{
			desc: 'verify select payment method label as "Select Payment Method"',
			selector: '.give-display-reveal .give-payment-mode-label',
			innerText: 'Select Payment Method',
		},

		{
			desc: 'verify test donation label as "Test Donation"',
			selector: '.give-display-reveal #give-gateway-option-manual',
			innerText: 'Test Donation',
		},

		{
			desc: 'verify offline donation label as "Offline Donation"',
			selector: '.give-display-reveal #give-gateway-option-offline',
			innerText: 'Offline Donation',
		},

		{
			desc: 'verify paypal donation label as "PayPal"',
			selector: '.give-display-reveal #give-gateway-option-paypal',
			innerText: 'PayPal',
		},

		{
			desc: 'verify manual payment radio with value "manual"',
			selector: '.give-display-reveal input[id^="give-gateway-manual"]',
			value: 'manual',
		},

		{
			desc: 'verify offline payment radio with value "offline"',
			selector: '.give-display-reveal input[id^="give-gateway-offline"]',
			value: 'offline',
		},

		{
			desc: 'verify paypal payment radio with value "paypal"',
			selector: '.give-display-reveal input[id^="give-gateway-paypal"]',
			value: 'paypal',
		},

		{
			desc: 'verify personal info title as "Personal Info"',
			selector: '.give-display-reveal #give_purchase_form_wrap legend',
			innerText: 'Personal Info',
		},

		{
			desc: 'verify first name label as "First Name"',
			selector: '.give-display-reveal label[for="give-first"]',
			innerText: 'First Name',
		},

		{
			desc: 'verify first name input field placeholder as "First Name"',
			selector: '.give-display-reveal #give-first',
			placeholder: 'First Name',
		},

		{
			desc: 'verify last name label as "Last Name"',
			selector: '.give-display-reveal label[for="give-last"]',
			innerText: 'Last Name',
		},

		{
			desc: 'verify last name input field placeholder as "Last Name"',
			selector: '.give-display-reveal #give-last',
			placeholder: 'Last Name',
		},

		{
			desc: 'verify company name label as "Company Name"',
			selector: '.give-display-reveal label[for="give-company"]',
			innerText: 'Company Name',
		},

		{
			desc: 'verify company name input field placeholder as "Company Name"',
			selector: '.give-display-reveal #give-company',
			placeholder: 'Company Name',
		},

		{
			desc: 'verify email address label as "Email Address"',
			selector: '.give-display-reveal label[for="give-email"]',
			innerText: 'Email Address',
		},

		{
			desc: 'verify email address input field placeholder as "Email Address"',
			selector: '.give-display-reveal #give-email',
			placeholder: 'Email Address',
		},

		{
			desc: 'verify anonymous donation label as "Make this an anonymous donation"',
			selector: '.give-display-reveal label[for="give-anonymous-donation"]',
			innerText: 'Make this an anonymous donation',
		},

		{
			desc: 'verify anonymous donation checkbox',
			selector: '.give-display-reveal #give-anonymous-donation',
		},

		{
			desc: 'verify comment label as "Comment"',
			selector: '.give-display-reveal label[for="give-comment"]',
			innerText: 'Comment',
		},

		{
			desc: 'verify comment textarea placeholder as "Leave a comment"',
			selector: '.give-display-reveal #give-comment',
			placeholder: 'Leave a comment',
		},

		{
			desc: 'verify create an account label as "Create an account"',
			selector: '.give-display-reveal label[for^="give-create-account"]',
			innerText: 'Create an account',
		},

		{
			desc: 'verify create an account checkbox',
			selector: '.give-display-reveal input[id^="give-create-account"]',
		},

		{
			desc: 'verify submit donation button value as "Make a Donation"',
			selector: '.give-display-reveal #give-purchase-button',
			value: 'Make a Donation',
		},
	])

	it( 'INTERACTION: fill first form and donate', async () => {
		await expect( page ).toClick( '.give-display-onpage button', { text: 'Silver' } )

		await expect( page ).toFillForm( '.give-display-onpage .give-form', {
			give_first: 'Ryan',
			give_last: 'Howard',
			give_email: 'ryan.howard@gmail.com',
		}, 10000 )

		await Promise.all([
			page.click( '.give-display-onpage .give-submit' ),
			page.waitForNavigation()
		])
	}, 100000 )

	it ( 'EXISTENCE: verify donation confirmation of the first form', async () => {
		await expect( page ).toMatch( 'Payment Complete: Thank you for your donation.' )
		await expect( page ).toMatch( 'Mr. Ryan Howard' )
		await expect( page ).toMatch( '$20.00' )
	})

	it( 'INTERACTION: redirect to the shortcode page', async () => {
		await Promise.all([
			page.goto( `${give.utility.vars.rootUrl}/give-donation-form-shortcode-with-same-forms-twice/` )
		])
	})

	it( 'INTERACTION: click button to reveal the 2nd form', async () => {
		await page.click( '.give-btn-reveal' )
	})

	it( 'INTERACTION: fill second form and donate', async () => {
		await expect( page ).toClick( '.give-display-reveal button', { text: 'Gold' } )

		await expect( page ).toFillForm( '.give-display-reveal .give-form', {
			give_first: 'Creed',
			give_last: 'Bratton',
			give_email: 'creed.bratton@gmail.com',
		}, 10000 )

		await Promise.all([
			page.click( '.give-display-reveal .give-submit' ),
			page.waitForNavigation()
		])
	}, 100000 )

	it ( 'EXISTENCE: verify donation confirmation of the second form', async () => {
		await page.waitFor( 10000 ); // Wait for receipt to load by ajax.
		await expect( page ).toMatch( 'Payment Complete: Thank you for your donation.' )
		await expect( page ).toMatch( 'Mr. Creed Bratton' )
		await expect( page ).toMatch( '$30.00' )
	}, 200000)
})
