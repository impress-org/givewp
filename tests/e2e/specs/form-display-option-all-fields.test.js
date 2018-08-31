const give = require( './test-utility' );

describe( 'Display Option: All fields', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/donations/simple-donation-form/` )

	})

	give.utility.fn.verifyDonationTitle( page, 'Simple Donation Form' )
	give.utility.fn.verifyCurrencySymbol( page, '$' )
	give.utility.fn.verifyCurrency( page, '10.00' )
	give.utility.fn.verifyFormContent( page )
	give.utility.fn.verifyDonationLevels( page )
	give.utility.fn.verifyPaymentMethods( page )
	give.utility.fn.verifyPersonalInfoFields( page )
	give.utility.fn.verifySubmitDonation( page )
})
