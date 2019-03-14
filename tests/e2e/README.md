# Frontend UI testing

The frontend UI testing tests whether the contents of Give's features display and function as intended. These tests don't focus on the aesthetics but rather tests the correct output on the webpage and the interactions with it.

## How to run the tests?

### Prerequisites
These tests run inside a dockerized container, so it is necessary that you have the [Docker Engine](https://docs.docker.com/install/) and [docker-compose](https://docs.docker.com/compose/install/) installed on your system.

A sample `wordpress.sql` is provided within the `/sample-data/` folder that has a few sample forms, donations, and donors to test in various combinations.

### Manual Testing
After setting up the local development environment, running tests manually is fairly simple. Navigate to the Give root folder where the `docker-compose.yml` file resides and set up the container by running:

```sh
docker-compose up -d
```

Now that the container is ready, run the tests by firing the following commad:
```sh
npm run test
```

By default this will run the tests in the headless mode.
If you wish to run the tests in non-headless mode, you can set `headless: false` inside `tests/e2e/jest-puppeteer.config.js`.
This will launch an instance of the Chrome browser and the tests will begin.

You can also change the speed of execution by setting `slowMo: 50`. The ideal speed range is between 30-80. _Low number indicates high speed_.

### Automation testing
The automation testing architecture is set within Travis where all the frontend tests run inside a Docker Container for the current branch on every pull request. For every pull request, a Docker Container is created which installs WordPress and sets up the database using the aforementioned SQL file. After running the tests, the Docker Container is destroyed automatically.

## How does it work?
Frontend UI tests in Give are written using Facebook's [Jest Javascript Framework](https://jestjs.io/). These tests run on [Puppeteer](https://developers.google.com/web/tools/puppeteer/) which is a [headless Chrome Node API](https://github.com/GoogleChrome/puppeteer).

The tests in Jest run parallely by default, but in Give, each test has been configured to run sequentially to avoid opening multiple tabs in Chrome at the same time.

The Puppeteer API is not designed for testing. To address this and to make testing easier, Give uses an Open Source testing framework built over Jest and Puppeteer called [Jest-Puppeteer](https://github.com/smooth-code/jest-puppeteer), which exposes the [assertion library](https://github.com/smooth-code/jest-puppeteer/blob/master/packages/expect-puppeteer/README.md#api) for puppeteer.
Most of the test cases uses functions provided by **expect-puppeteer** for ease of testing, and at some places it directly uses functions provided by Puppeteer API itself.

## What does it test?
The frontend tests are bifurcated into 2 types
#### EXISTENCE TESTS
These tests are assertion tests which compare the expected output with the output found on the HTML DOM for a specific element. This also tests whether an HTML element that is expected to be part of the DOM is present in the DOM.  

An example of existence test:

```JS
give.utility.fn.verifyExistence( page, [
	{
		desc: 'verify form title',
		selector: '.give-form-title',
		strict: true
		innerText: 'Simple Donation Form',
	}
])
```
The following 3 object properties **describe** the test, **what** to test, and **how** to test. These properties will be deleted from the object just before assertion test begins.

- `desc`: Desciption of the test. This will be output on the terminal screen.
- `selector`: The selector which needs to be tested.
- `strict`: Setting this to `true` will use [toBe()](https://jestjs.io/docs/en/expect#tobevalue), else it will use [toMatch()](https://jestjs.io/docs/en/expect#tomatchregexporstring). Default: `false`.

The 4th object property is `innerText` which is one of the many HTML node attributes. You can pass as many attributes you wish to test, it could be `href`, `value` and `innerHTML`; etc.

#### INTERACTION TESTS
These tests test the interaction with the webpage. This can be better visualized after setting the `screenshot` parameter to true which will generate a screenshot after every interaction.

It provides 3 types of interaction
- hover
- focus
- click

An example of interaction test:

```JS
give.utility.fn.verifyInteraction( page, [
	{
		desc: 'verify hover on title tooltip',
		selector: 'label[for="give-title"] .give-tooltip',
		event: 'hover',
	}
])
```
The above test will hover the mouse pointer over the label which has the `for` attribute set as `give-title`

## How to write tests?
- Test files should end with `.test.js` suffix
- [babel-jest](https://www.npmjs.com/package/babel-jest) is added to support ES6 syntax
- `test-utility.js` file contains helper functions and variables that should be used across all tests.
- The URL to test should be set within the [beforeAll()](https://jestjs.io/docs/en/api#beforeallfn-timeout) method
- Priority should be given to [expect-puppeteer](https://github.com/smooth-code/jest-puppeteer/blob/master/packages/expect-puppeteer/README.md#api) followed by [Puppeteer API](https://github.com/GoogleChrome/puppeteer/blob/master/docs/api.md#puppeteer-api-tip-of-tree). There are few bugs that produces [race condition](https://en.wikipedia.org/wiki/Race_condition) which causes tests to fail due to unresolved Promises. [link#1](https://github.com/GoogleChrome/puppeteer/issues/1412#issuecomment-345287522), [link#2](https://github.com/GoogleChrome/puppeteer/issues/1412#issuecomment-345294748), [link#3](https://github.com/GoogleChrome/puppeteer/issues/1412#issuecomment-345299369), [link#4](https://github.com/GoogleChrome/puppeteer/issues/1412#issuecomment-402725036)

If the tests contain any action or event that might lead to redirection/navigation, for example after a form submission like:

```JS
page.click( '.form-submit' )
page.waitForNavigation()
```

This is known to cause a race condition. The following must be used as a workaround:

```JS
await Promise.all([
	page.click( '#give_login_submit' ),
	page.waitForNavigation()
])
```

## Documenting tests
For the ease of understanding, it will be helpful if you follow a naming convention to name the test files. For example, if the test is about the Give Form Shortcode, then a file name as `shortcode-give-form.test.js` gives a fair idea about the test.

Each test file should begin with a desription of the test, following with a brief explanantion of what areas it tests.
If the test file performs both EXISTENCE and INTERACTION tests, then break down the 2 into separate regions explaining
what is does, for example:

```JS
/**
 * This test performs EXISTENCE and INTERACTION tests for the shortcode [give_form_grid]
 *
 * For EXISTENCE tests, it tests for
 * - Grid item title
 * - Grid item form content
 *
 * For INTERACTION tests, it tests for
 * - hover to test the hover animation
 * - click on the grid-item to open the popup
 * - clicks the close button to close the popup
 */
```

Add single line comments wherever there are events such as form submission and redirection.

## Resources
1. [jest-puppeteer](https://github.com/smooth-code/jest-puppeteer)
2. [expect-puppeteer](https://github.com/smooth-code/jest-puppeteer/blob/master/packages/expect-puppeteer/README.md#api)
3. [Puppeteer API](https://github.com/GoogleChrome/puppeteer/blob/master/docs/api.md#puppeteer-api-tip-of-tree)
4. [Jest](https://jestjs.io/docs/en/getting-started)
