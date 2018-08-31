const helpers = {
	vars: {
		rootUrl: 'http://localhost:8004',
		firstName: 'Devin',
		lastName: 'Walker',
		email: 'devin.walker@gmail.com',
		company: 'WordImpress',
		comment: 'Glad to be a part!'
	},
	fn: {
		/**
		 * Function to login to WordPress.
		 *
		 * @param {object} page                 Puppeteer page object.
		 * @param {object} credentials          Login credentials object.
		 * @param {string} credentials.username WordPress Login Username.
		 * @param {string} credentials.password WordPress Login Password.
		 */
		logIn: function( page, credentials ) {
			it( 'Login to WordPress', async () => {

				// If `#wpadminbar` is absent on the page, means that we are not logged in. 
				if ( null === await page.$( '#wpadminbar' ) ) {

					// Go to the /wp-admin page to login to WordPress.
					await page.goto( `${helpers.vars.rootUrl}/wp-admin` )

					// Wait for the login form to appear after visiting /wp-admin.
					page.waitForSelector( 'form[id="loginform"]' )

					// Fill the login form with the username and password values.
					await expect( page ).toFillForm( 'form[id="loginform"]', {
						log: credentials.username,
						pwd: credentials.password,
					}, {
						// This is a preventive measure in case if the form is not filled quickly.
						timeout: 2000
					})

					/* Redirection after submission leads to race condition (known bug), below is
					 * the workaround.
					 */
					await Promise.all([
						await page.click( '#wp-submit' ),
						await page.waitForNavigation( { timeout: 500000 } ),
					])
				}
			})
		},

		/**
		 * Function to logout of WordPress
		 *
		 * @param {object} page Puppeteer page object.
		 */
		logOut: function( page ) {
			it( 'Logout of WordPress', async () =>  {

				// Get the Logout link.
				const logoutLink = await page.evaluate( ()  => {
					return document.querySelector( '#wp-admin-bar-logout a' ).href
				})

				// Visiting the link will logout from WordPress.
				await page.goto( logoutLink )
			})
		},

		/**
		 * This function checks whether an element is present on the page or not.
		 * Also checks if the given text node is found on the page.
		 *
		 * Comparison is strict if the `strict` key is set to `true`
		 *
		 * @param {object} page                     Puppeteer page object.
		 * @param {array}  elementArray             Array of objects which contain the test data. 
		 * @param {string} elementArray.desc        Description of the test. 
		 * @param {string} elementArray.selector    Name of the selector. 
		 * @param {string} elementArray.strict      If set to true, toBe() will be used, else toMatch(). 
		 * @param {string} elementArray.<attribute> Value of the <attribute> to compare. 
		 */
		verifyExistence: function( page, elementArray = [] ) {
			for( let object of elementArray ) {

				// Test begins from here.
				it( `EXISTENCE: ${object.desc}`, async () => {

					const selector = object.selector
					let strict   = ''

					/**
					 * We delete the desc, selector and strict keys from the object because
					 * we only want to test the HTML node attributes. For example `value`
					 * `innerText`, `innerHTML`, etc.
					 */
					delete object.desc
					delete object.selector

					/**
					 * The object does not contain the `strict` property, then set
					 * `strict` to `false` by default.
					 */
					if ( object.hasOwnProperty( 'strict' ) ) {
						strict = object.strict
						delete object.strict
					} else {
						strict = false;
					}

					if ( 0 < Object.keys( object ).length ) {
						for( let prop in object ) {

							// Get the value of the attribute.
							const value = await page.evaluate( ( selector, prop ) => {
								return document.querySelector( selector )[prop]
							}, selector, prop )

							if ( strict ) {

								/**
								 * This will perform a strict case-sensitive comparison.
								 */
								await expect( value ).toBe( object[prop] )

							} else {

								/**
								 * This will perform a loose case-sensitive comparison.
								 * Checks for substring.
								 */
								await expect( value ).toMatch( object[prop] )

							}
						}
					} else {

						/**
						 * If no HTML node attribute is found in the object, it means that
						 * we have to check whether the element exists in the HTML DOM. 
						 */
						await expect( page ).toMatchElement( selector )

					}
				})
			}
		},

		/**
		 * Runs interaction tests on elements.
		 *
		 * @param {object} page                  Puppeteer page object.
		 * @param {array}  elementArray          Array of object which contains the data that needs to be tested.
		 * @param {string} elementArray.desc     Description of the test. 
		 * @param {string} elementArray.selector Name of the selector. 
		 * @param {string} elementArray.action   Actions such as hover, click and focus. 
		 */
		verifyInteraction: function( page, elementArray ) {
			for( let object of elementArray ) {
				it( `INTERACTION: ${object.desc}`, async () => {
					const element = await page.$( object.selector )

					switch( object.event ) {
						case 'click':
							await element.click()
							break

						case 'hover':
							await element.hover()
							break

						case 'focus':
							await element.focus()
							break
					}
				})
			}
		},

		/**
		 * This function makes a sample donation.
		 *
		 * @param {object} page          Puppeteer page object.
		 * @param {object} formDetails   Form object.
		 * @param {string} paymentMethod HTML "id" of the payment method option.
		 */
		makeDonation: function( page, formDetails = {}, paymentMethod = 'give-gateway-option-manual' ) {
			it( 'INTERACTION: make a donation', async () =>  {

				// Select the payment method.
				await page.click( `label[id="${paymentMethod}"]` )

				// Click the button to enter custom donation amount.
				await page.click( '.give-btn-level-custom' )

				// Wait for custom amount input field to load.
				await page.waitForSelector( '.give-btn-level-custom' )

				// Fill custom amount input field with value '23.54'
				await expect( page ).toFill( 'input[name="give-amount"]', '23.54' )

				// Fill the form fields.
				await expect( page ).toFillForm( 'form[id^="give-form"]', formDetails, { timeout: 10000 } )

				// Select donor title.
				await expect( page ).toSelect( 'select[name="give_title"]', 'Dr.' )

				// Submit the donation form and wait for navigation.
				await Promise.all([
					page.click( '#give-purchase-button' ),
					page.waitForNavigation()
				])
			})	
		},

		/**
		 * This function is used to assert values on the donation
		 * confirmation page.
		 *
		 * @param {object} page     Puppeteer page object.
		 * @param {array}  matchers Puppeteer page object.
		 */
		verifyDonation: function( page, matchers = [] ) {

			// Check if we're on /donation-confirmation page.
			it( 'EXISTENCE: verify donation confirmation URL', async () => {
				const donationConfirmationUrl = await page.url()

				await expect( donationConfirmationUrl ).toBe( `${helpers.vars.rootUrl}/donation-confirmation/` )
			})

			// Match every text node on /donation-confirmation page.
			for( let matcher of matchers ) {
				it( `EXISTENCE: verify the donation confirmation page for "${matcher}"`, async () => {
					await expect( page ).toMatch( matcher )
				})
			}
		}
	}
}

exports.utility = helpers;
