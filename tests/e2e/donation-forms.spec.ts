import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {editForm, section} from './utils/form';
import {captureOffsiteRedirect, enableTestOffsiteGateway, signedReturnUrl} from './utils/offsite-gateway';
import {
    DONOR,
    donationForm,
    expectReceipt,
    fillDonorDetails,
    payWithTestGateway,
    waitForForm,
} from './utils/donation-form';

/**
 * A v3 donation form on the front end - the donor's half of it.
 *
 * The form is a React app served on its own route and pulled onto the page inside an iframe, and
 * its two server routes - `validate` on each step, `donate` on submit - are signed `givewp-route`
 * requests rather than REST. None of that is reachable from PHPUnit, so a donation walked through
 * the real embed is the only place a broken signature, a step that no longer advances, or a form
 * that mounts empty shows up.
 */

/*
 * Donors are not logged in, and the form behaves differently for someone who is: the email field
 * comes prefilled from the account, and the donation attaches to that user. Run these as the
 * public, which is who they are about.
 */
test.use({storageState: {cookies: [], origins: []}});

test.describe('V3 donation forms', () => {
    test.describe('the default form', () => {
        let formId: number;

        test.beforeAll(async ({requestUtils}) => {
            ({formId} = await createCampaignWithForm(requestUtils));
        });

        test.beforeEach(async ({page}) => {
            await page.goto(`/?post_type=give_forms&p=${formId}`);
            await waitForForm(donationForm(page));
        });

        test('takes a donation through to the receipt', async ({page}) => {
            const form = donationForm(page);

            /*
             * Three steps: amount, donor, payment. The amount step opens with a level already
             * selected, so it only needs the button. Each step posts to the `validate` route before
             * advancing, which is why reaching the next step's fields proves that route answered.
             */
            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$10.00');
        });

        test('donates the level the donor picked', async ({page}) => {
            const form = donationForm(page);

            await form.getByRole('radio', {name: '$50.00'}).click();
            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await expect(form.locator('.givewp-elements-donationSummary')).toContainText('$50.00');

            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$50.00');
        });

        test('donates a custom amount', async ({page}) => {
            const form = donationForm(page);

            await form.getByLabel('Enter custom amount').fill('73');
            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$73.00');
        });

        test('will not advance past the donor step without a name and email', async ({page}) => {
            const form = donationForm(page);

            await form.getByRole('button', {name: 'Donate now'}).click();
            await form.getByRole('button', {name: 'Continue'}).click();

            await expect(form.locator('#givewp-field-error-firstName')).toBeVisible();
            await expect(form.locator('#givewp-field-error-email')).toBeVisible();

            // Still on the donor step rather than the payment step.
            await expect(form.getByLabel('First name')).toBeVisible();
        });

        test('rejects a malformed email address', async ({page}) => {
            const form = donationForm(page);

            await form.getByRole('button', {name: 'Donate now'}).click();

            await form.getByLabel('First name').fill(DONOR.firstName);
            await form.getByLabel('Email Address').fill('ada@example');
            await form.getByRole('button', {name: 'Continue'}).click();

            await expect(form.locator('#givewp-field-error-email')).toBeVisible();
        });

        test('keeps what the donor entered when they step back', async ({page}) => {
            const form = donationForm(page);

            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await expect(form.locator('.givewp-fields-gateways__list')).toBeVisible();

            await form.getByRole('button', {name: 'Previous'}).click();

            await expect(form.getByLabel('First name')).toHaveValue(DONOR.firstName);
            await expect(form.getByLabel('Email Address')).toHaveValue(DONOR.email);
        });
    });

    /**
     * Each design is a separate React layout over the same form data. Classic renders every section
     * on one page rather than as steps, which is a different component tree reaching the same two
     * routes, so a donation is the only assertion that covers both halves of the difference.
     */
    test.describe('form designs', () => {
        test('classic renders every section on one page and donates', async ({page, requestUtils}) => {
            const {formId} = await createCampaignWithForm(requestUtils);

            await editForm(requestUtils, formId, (form) => {
                form.settings.designId = 'classic';
            });

            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);
            await waitForForm(form);

            await fillDonorDetails(form);
            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$10.00');
        });

        test('two-panel steps donates', async ({page, requestUtils}) => {
            const {formId} = await createCampaignWithForm(requestUtils);

            await editForm(requestUtils, formId, (form) => {
                form.settings.designId = 'two-panel-steps';
            });

            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);
            await waitForForm(form);

            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$10.00');
        });
    });

    /**
     * The blocks a fundraiser can add to a form beyond the four it cannot do without. Each one is a
     * block the builder writes, a field the form renders, and a column on the donation, and only the
     * middle of those three is visible from the browser - so the donation is read back over the REST
     * API to prove what the donor typed actually landed.
     */
    test.describe('optional fields', () => {
        let campaignId: number;
        let formId: number;

        test.beforeAll(async ({requestUtils}) => {
            ({campaignId, formId} = await createCampaignWithForm(requestUtils));

            await editForm(requestUtils, formId, (form) => {
                section(form, "Who's Giving Today?").innerBlocks.push(
                    {name: 'givewp/company', attributes: {label: 'Company Name', isRequired: false}, innerBlocks: []},
                    {
                        name: 'givewp/donor-comments',
                        attributes: {label: 'Comment', description: 'Would you like to add a comment?'},
                        innerBlocks: [],
                    },
                    {
                        name: 'givewp/anonymous',
                        attributes: {label: 'Make this an anonymous donation.'},
                        innerBlocks: [],
                    }
                );

                section(form, 'Payment Details').innerBlocks.push({
                    name: 'givewp/terms-and-conditions',
                    attributes: {
                        useGlobalSettings: false,
                        checkboxLabel: 'I agree to the Terms and conditions.',
                        displayType: 'showFormTerms',
                        linkText: 'Show terms',
                        linkUrl: '',
                        agreementText: 'These are the terms.',
                        modalHeading: 'Do you consent to the following',
                        modalAcceptanceText: 'Accept',
                    },
                    innerBlocks: [],
                });
            });
        });

        test('will not submit until the terms are accepted', async ({page}) => {
            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);
            await waitForForm(form);

            await form.getByRole('button', {name: 'Donate now'}).click();
            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await payWithTestGateway(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expect(form.locator('[id^="givewp-field-error-consent"]')).toBeVisible();
        });

        test('records what the donor typed into them', async ({page, requestUtils}) => {
            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);
            await waitForForm(form);

            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByLabel('Company Name').fill('Analytical Engines');
            await form.getByLabel('Comment').fill('Keep up the good work.');
            await form.getByLabel('Make this an anonymous donation.').check();
            await form.getByRole('button', {name: 'Continue'}).click();

            await payWithTestGateway(form);
            await form.getByLabel('I agree to the Terms and conditions.').check();
            await form.getByRole('button', {name: 'Donate now'}).click();

            await expectReceipt(form, '$10.00');

            const [donation] = await requestUtils.rest<any[]>({
                path: `/givewp/v3/donations?campaignId=${campaignId}&mode=test&anonymousDonations=include&includeSensitiveData=true`,
            });

            expect(donation).toMatchObject({
                company: 'Analytical Engines',
                comment: 'Keep up the good work.',
                anonymous: true,
            });
        });
    });

    /**
     * Settings that only change what the donor sees. They are read by the form app rather than by
     * the routes, so a rendered form is the only place they can be checked.
     */
    test.describe('form settings', () => {
        test('shows the heading, goal and donate button the form was given', async ({page, requestUtils}) => {
            const {formId} = await createCampaignWithForm(requestUtils);

            await editForm(requestUtils, formId, (form) => {
                form.settings.showHeader = true;
                form.settings.heading = 'Fund the difference engine';
                form.settings.enableDonationGoal = true;
                form.settings.donateButtonCaption = 'Give today';
            });

            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);
            await waitForForm(form);

            /*
             * A form with a header shows it as a step of its own, ahead of the amount step, so the
             * donor passes through one more step than the default form has.
             */
            await expect(form.getByText('Fund the difference engine')).toBeVisible();
            await expect(form.locator('.givewp-layouts-goal__progress__meter')).toBeVisible();

            await form.getByRole('button', {name: 'Donate now'}).click();
            await form.getByRole('button', {name: 'Continue'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await expect(form.getByRole('button', {name: 'Give today'})).toBeVisible();
        });
    });

    /**
     * How the form gets onto someone else's page. Each format is a different branch of the embed
     * app over the same iframe, and the shortcode is the path add-ons and older sites still use.
     */
    test.describe('embed formats', () => {
        let formId: number;

        test.beforeAll(async ({requestUtils}) => {
            ({formId} = await createCampaignWithForm(requestUtils));
        });

        test('a shortcode embeds the form on a page', async ({page, requestUtils}) => {
            const post = await requestUtils.createPost({
                title: 'Shortcode embed',
                content: `[give_form id="${formId}"]`,
                status: 'publish',
            });

            await page.goto(post.link);

            await waitForForm(donationForm(page));
        });

        test('the modal format opens the form in a dialog', async ({page, requestUtils}) => {
            const post = await requestUtils.createPost({
                title: 'Modal embed',
                content: `[give_form id="${formId}" display_style="modal" continue_button_title="Donate now"]`,
                status: 'publish',
            });

            await page.goto(post.link);

            await page.getByRole('button', {name: 'Open donation form'}).click();

            await waitForForm(donationForm(page));
        });

        test('the new tab format links to the form page', async ({page, requestUtils}) => {
            const post = await requestUtils.createPost({
                title: 'New tab embed',
                content: `[give_form id="${formId}" display_style="newTab" continue_button_title="Donate now"]`,
                status: 'publish',
            });

            await page.goto(post.link);

            await expect(page.locator('a.givewp-donation-form-link')).toHaveAttribute(
                'href',
                new RegExp(`p=${formId}`)
            );
        });
    });
    /*
     * An offsite gateway takes the donor away and brings them back on a signed return URL. The form's
     * half of that is a redirect out of the iframe, and the return URL it signs is the receipt route on
     * the page the form was embedded on, query string and all. See legacy-donation-forms.spec.ts for the
     * v2 form's version of the same trip.
     */
    test.describe('paying through an offsite gateway', () => {
        let formId: number;

        test.beforeAll(async ({requestUtils}) => {
            enableTestOffsiteGateway(3);
            ({formId} = await createCampaignWithForm(requestUtils));
        });

        test('takes the donation out to the gateway and back to the receipt', async ({page}) => {
            await page.goto(`/?post_type=give_forms&p=${formId}`);

            const form = donationForm(page);

            await waitForForm(form);
            await form.getByRole('button', {name: 'Donate now'}).click();

            await fillDonorDetails(form);
            await form.getByRole('button', {name: 'Continue'}).click();

            await form.getByRole('radio', {name: 'Donate with Test Gateway (Offsite)'}).check();

            const offsiteUrl = await captureOffsiteRedirect(page, async () => {
                await form.getByRole('button', {name: 'Donate now'}).click();
            });

            const returnUrl = signedReturnUrl(offsiteUrl);

            expect(returnUrl).toContain('givewp-event=donation-completed');
            expect(returnUrl).toContain('givewp-receipt-id=');

            // Back from the processor, in the donor's own window.
            await page.goto(offsiteUrl);

            await expect(page).toHaveURL(/givewp-event=donation-completed/);
            await expectReceipt(donationForm(page), '$10.00');
        });
    });
});
