const give = require( './test-utility' );

describe( 'Shortcode Register', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/give-register-shortcode/` )

	})

	it( 'verify legend of registration form', async () => {
		await expect( page ).toMatchElement( '#give-register-form legend', { text: 'Register a New Account' } )
	})

	give.utility.fn.verifyInputFields(
		page,
		'verify presence of login form fields',
		[
			'#give-user-login',
			'#give-user-email',
			'#give-user-pass',
			'#give-user-pass2',
			'input[name="give_register_submit"]',
		]
	)
})