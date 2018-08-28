const give = require( './test-utility' );

describe( 'Display option: Modal', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/donations/modal-form/` )

	})

	give.utility.fn.verifyDonationTitle( page, 'Modal Form' )
	give.utility.fn.verifyCurrencySymbol( page, '$' )
	give.utility.fn.verifyCurrency( page, '10.00' )
	give.utility.fn.verifyDonationLevels( page )
	give.utility.fn.verifyPaymentMethods( page )
	give.utility.fn.verifyFormContent( page, 'Form Content of the Reveal Form. Click on “Donate Now” for the form to popup. This was a long description.' )
	give.utility.fn.verifyInputFields(
		page,
		'verify presence of personal info form fields',
		[
			'#give-title',
			'#give-first',
			'#give-last',
			'#give-company',
			'#give-email',
			'#give-anonymous-donation',
			'#give-comment',
			'input[name="give_create_account"]',
			'.give-checkout-login',
		]
	)
})
