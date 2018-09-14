/**
 * This test performs EXISTENCE and INTERACTION tests on the shortcode [give_login]
 *
 * For EXISTENCE tests, it tests for
 * - All the labels and fields of the form
 * - Checks if the reset password link is correct
 * - verifies if login is successful
 *
 * For INTERACTION tests, it
 * - fills the forms
 * - submits the form
 */

const give = require( './test-utility' );

describe( 'Shortcode Login', () => {

	// Visit the /give-login-shortcode page.
	beforeAll( async () => await page.goto( `${give.utility.vars.rootUrl}/give-login-shortcode/` ) )

	give.utility.fn.verifyExistence( page, [

		{
			desc: 'verify form legend as "Log into Your Account"',
			selector: '#give-login-form legend',
			innerText: 'Log into Your Account',
		},

		{
			desc: 'verify username label as "Username"',
			selector: 'label[for="give_user_login"]',
			innerText: 'Username',
		},

		{
			desc: 'verify username text input field',
			selector: '#give_user_login',
		},

		{
			desc: 'verify password label as "Password"',
			selector: 'label[for="give_user_pass"]',
			innerText: 'Password',
		},

		{
			desc: 'verify password text input field',
			selector: '#give_user_pass',
		},

		{
			desc: 'verify login button as "Log In"',
			selector: '#give_login_submit',
			value: 'Log In'
		},

		{
			desc: 'verify password reset link as "Reset Password"',
			selector: `a[href="${give.utility.vars.rootUrl}/wp-login.php?action=lostpassword"]`,
			innerText: 'Reset Password',
		},
	])

	it( 'INTERACTION: login through shortcode', async () => {
		await expect( page ).toFillForm( '#give-login-form', {
			give_user_login: 'sam.smith@gmail.com',
			give_user_pass: 'sam12345',
		})

		await Promise.all([
			page.click( '#give_login_submit' ),
			page.waitForNavigation()
		])
	})

	it( 'EXISTENCE: verify login success', async () => {
		await expect( page ).toMatchElement( '.display-name', { text: 'Samuel' } )
	})

	// Logout of WordPress if all the tests have completed.
	afterAll( async () => {
		const logoutLink = await page.evaluate( ()  => {
			return document.querySelector( '#wp-admin-bar-logout a' ).href
		})

		await page.goto( logoutLink )
	})
})
