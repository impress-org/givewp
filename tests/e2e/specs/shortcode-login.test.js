const give = require( './test-utility' );

describe( 'Shortcode Login', () => {

	beforeAll( async () => {
		// Visit the Set donation form page.
		await page.goto( `${give.utility.vars.rootUrl}/give-login-shortcode/` )

	})

	it( 'verify legend of login form', async () => {
		await expect( page ).toMatchElement( '#give-login-form legend', { text: 'Log into Your Account' } )
	})

	give.utility.fn.verifyInputFields(
		page,
		'verify presence of registration form fields',
		[
			'#give_user_login',
			'#give_user_pass',
			'#give_login_submit',
		]
	)

	it( 'verify password reset link', async () => {
		await expect( page ).toMatchElement( 'a[href="http://localhost:8004/wp-login.php?action=lostpassword"]', { text: 'Reset Password' } )
	})
})

