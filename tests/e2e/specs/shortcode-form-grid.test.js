const give = require( './test-utility' );

describe( 'Shortcode: Form Grid', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/form-grid/` )

	})

	give.utility.fn.verifyDonationTitle( page, 'Button Form' )
	give.utility.fn.verifyDonationTitle( page, 'Reveal Form' )
	give.utility.fn.verifyDonationTitle( page, 'Modal Form' )
	give.utility.fn.verifyDonationTitle( page, 'Simple Donation Form' )
	give.utility.fn.verifyFormContent( page, 'Form Content of the Button Form.' )
	give.utility.fn.verifyFormContent( page, 'Form Content of the Reveal Form. A moderately long description.' )
	give.utility.fn.verifyFormContent( page, 'Form Content of the Reveal Form. Click on "Donate Now" for the form to popup. This…' )
	give.utility.fn.verifyFormContent( page, 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and…' )
	give.utility.fn.verifyCurrencySymbol( page, '$' )
	give.utility.fn.verifyCurrency( page, '10.00' )
	give.utility.fn.verifyDonationLevels( page )
	give.utility.fn.verifyPaymentMethods( page )
	// give.utility.fn.verifyPersonalInfoFields( page )
	give.utility.fn.verifyElementCount( page, { '.give-grid__item': 4 })
})
