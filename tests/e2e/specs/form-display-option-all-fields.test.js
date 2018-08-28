const give = require( './test-utility' );

describe( 'Display Option: All fields', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/donations/simple-donation-form/` )

	})

	give.utility.fn.verifyDonationTitle( page, 'Simple Donation Form' )
	give.utility.fn.verifyCurrencySymbol( page, '$' )
	give.utility.fn.verifyCurrency( page, '10.00' )
	give.utility.fn.verifyFormContent( page, 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and practice. The Army’s doctrine follows the mainstream of Christian belief and its articles of faith emphasise God’s saving purposes.' )
	give.utility.fn.verifyDonationLevels( page )
	give.utility.fn.verifyPaymentMethods( page )
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
