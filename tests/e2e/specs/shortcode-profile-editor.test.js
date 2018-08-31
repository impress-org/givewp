const give = require( './test-utility' );

describe( 'Shortcode Profile Editor', () => {

	beforeAll( async () => {
		await page.goto( `${give.utility.vars.rootUrl}/give-profile-editor-shortcode/` )
	})

	// it( '', async () => {})

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

	it( 'INTERACTION: verify change password', async () => {
		await page.goto( `${give.utility.vars.rootUrl}/give-profile-editor-shortcode/` )

		await expect( page ).toFillForm( '#give_profile_editor_form', {
			give_new_user_pass1: 'sam12345',
			give_new_user_pass2: 'sam12345',
		})

		await Promise.all([
			page.click( '#give_profile_editor_submit' ),
			page.waitForNavigation()
		])
	})

	it( 'EXISTENCE: verify success password change', async () => {
		await expect( page ).toMatch( 'Your password has been updated.' )
	})

	// Logout of WordPress.
	afterAll( async () => {
		const logoutLink = await page.evaluate( ()  => {
			return document.querySelector( '#wp-admin-bar-logout a' ).href
		})

		page.goto( logoutLink )
	})
})
