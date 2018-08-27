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
		 * Checks if the donation form title is correct.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page  Puppeteer page object.
		 * @param {string} title Form title string.
		 */
		verifyDonationTitle: function( page, title ) {
			it( `verify form title to be "${title}"`, async () => {
				await expect( page ).toMatch( title )
			})
		},

		/**
		 * Checks if the donation form currency symbol is correct.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page   Puppeteer page object.
		 * @param {string} symbol Form currency symbol.
		 */
		verifyCurrencySymbol: function( page, symbol ) {
			it( `verify currency symbol to be "${symbol}"`, async () => {
				const value = await page.$( '.give-currency-symbol' )
				await expect( value ).toMatch( symbol )
			})
		},

		/**
		 * Checks if the donation form currency is correct.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page     Puppeteer page object.
		 * @param {string} currency Form currency value.
		 */
		verifyCurrency: function( page, currency ) {
			it( `verify amount to be "${currency}"`, async () => {
				const value = await page.evaluate( () => document.querySelector( '#give-amount' ).value )
				await expect( value ).toBe( currency )
			})
		},

		/**
		 * Checks if the donation form content is correct.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page Puppeteer page object.
		 */
		verifyFormContent: function( page ) {
			it( 'verify form content', async () => {
				await expect( page ).toMatch( 'The Salvation Army is an integral part of the Christian Church, although distinctive in government and practice. The Army’s doctrine follows the mainstream of Christian belief and its articles of faith emphasise God’s saving purposes.' )
			})	
		},

		/**
		 * Checks if the form donation levels are correct. Also checks the placeholder for custom amount field.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page Puppeteer page object.
		 */
		verifyDonationLevels: function( page ) {
			it( 'verify donation levels with custom price button', async () => {
				const buttonOne   = await page.$( '.give-btn-level-0' )
				const buttonTwo   = await page.$( '.give-btn-level-1' )
				const buttonThree = await page.$( '.give-btn-level-2' )
				const buttonFour  = await page.$( '.give-btn-level-custom' )

				await expect( buttonOne ).toMatch( 'Bronze' )
				await expect( buttonTwo ).toMatch( 'Silver' )
				await expect( buttonThree ).toMatch( 'Gold' )
				await expect( buttonFour ).toMatch( 'or donate what you like!' )
			})
		},

		/**
		 * Checks if the form donation Payment Methods are correct.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page Puppeteer page object.
		 */
		verifyPaymentMethods: function( page ) {
			it( 'verify 3 payment methods', async () => {
				const test    = await page.$( '#give-gateway-option-manual' )
				const offline = await page.$( '#give-gateway-option-offline' )
				const paypal  = await page.$( '#give-gateway-option-paypal' )

				await expect( test ).toMatch( 'Test Donation' )
				await expect( offline ).toMatch( 'Offline Donation' )
				await expect( paypal ).toMatch( 'PayPal' )
			})
		},

		/**
		 * Checks if all the Personal Info fields are present on the donation form.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page Puppeteer page object.
		 */
		verifyPersonalInfoFields: function( page ) {
			it( 'verify all fields for Personal Info', async () => {
				const mr    = await page.evaluate( () => document.querySelector( '#give-title' ).options[0].innerText )
				const mrs   = await page.evaluate( () => document.querySelector( '#give-title' ).options[1].innerText )
				const ms    = await page.evaluate( () => document.querySelector( '#give-title' ).options[2].innerText )
				const dr    = await page.evaluate( () => document.querySelector( '#give-title' ).options[3].innerText )
				const value = await page.evaluate( () => document.querySelector( '#give-purchase-button' ).value )	

				await expect( page ).toMatchElement( '#give-title' )
				await expect( page ).toMatchElement( '#give-first' )
				await expect( page ).toMatchElement( '#give-last' )
				await expect( page ).toMatchElement( '#give-company' )
				await expect( page ).toMatchElement( '#give-email' )
				await expect( page ).toMatchElement( '#give-anonymous-donation' )
				await expect( page ).toMatchElement( '#give-comment' )

				await expect( mr ).toBe( 'Mr.' )
				await expect( mrs ).toBe( 'Mrs.' )
				await expect( ms ).toBe( 'Ms.' )
				await expect( dr ).toBe( 'Dr.' )
				await expect( value ).toBe( 'Make a Donation' )
			})
		},

		/**
		 * Fills all the form fields and submits the donation form.
		 *
		 * @since 2.3.0
		 *
		 * @param {Object} page Puppeteer page object.
		 */
		verifySubmitDonation: function( page ) {
			it( 'Fill all fields and donate', async () => {
				await expect( page ).toClick( 'button', { text: 'or donate what you like!' } )
				await expect( page ).toFillForm( '.give-form', {
					'give-amount': '35.45',
					'give_first': helpers.vars.firstName,
					'give_last': helpers.vars.lastName,
					'give_company_name': helpers.vars.company,
					'give_email': helpers.vars.email,
					'give_comment': helpers.vars.comment,
				})
				await page.click( '#give-purchase-button' )
				await page.waitForNavigation()
			}, 500000) 
		}
	}
}

exports.utility = helpers;