/**
 * This test performs EXISTENCE and INTERACTION for Give API
 *
 * The EXISTENCE test will test for
 * - API endpoint exist
 * - get valid error message on API endpoint
 *
 * The INTERACTION test
 * - Review JSON result output for donations endpoint
 */

const give = require('./test-utility');

describe('GiveAPI', () => {
	const apiKey   = 'c7f8e6d3a85b83c6bfe663a820dc54b8',
		  apiToken = '2b97f886f792034f071c0386c9b027f9';

	const apiEndpoints = {
		donations: `donations?key=${apiKey}&token=${apiToken}`
	};

	// Verify default endpoint.
	it( 'EXISTENCE: get error when query give-api/v1 endpoint without key and token', async () => {
		await page.goto( `${give.utility.vars.rootUrl}/give-api/v1` );
		const apiResponse = await page.evaluate(() =>  {
			return JSON.parse(document.querySelector('body' ).innerText );
		});

		expect( { error: 'Invalid query.' } ).toEqual( apiResponse );
	});

	// Verify default endpoint error when do not pass required params.
	it( 'INTERACTION: get error when do not pass required key or token', async () => {
		await page.goto( `${give.utility.vars.rootUrl}/give-api/v1/donations?key=${apiKey}&toke=${apiToken}` );
		const apiResponse = await page.evaluate(() =>  {
			return JSON.parse(document.querySelector('body' ).innerText );
		});

		expect( {error: 'You must specify both a token and API key.'} ).toEqual( apiResponse );
	});

	// @todo: add test when pass wrong value for either key or token

	// Verify donations endpoint.
	it('INTERACTION: verify result for give-api/v1/donations endpoint', async () => {
		// enable request interception.
		await page.setRequestInterception(true);

		// add header for the navigation requests.
		page.on('request', request => {
			// Do nothing in case of non-navigation requests.
			if (!request.isNavigationRequest()) {
				request.continue();
				return;
			}
			// Add a new header for navigation request.
			const headers = request.headers();
			headers['Accept'] = 'application/json';
			request.continue({headers});
		});

		// navigate to the website.
		await page.goto(`${give.utility.vars.rootUrl}/give-api/v1/${apiEndpoints.donations}`);

		let apiResponse = await page.evaluate(() => {
			try{
				return JSON.parse(document.querySelector('body').innerText);
			} catch ( e ) {
				return { 'customJSONSytaxError': 'JSON result does not formatted' };
			}
		});

		apiResponse = Object.keys( apiResponse );
		expect( apiResponse ).toEqual( [ 'donations', 'request_speed' ] );
	});

})
