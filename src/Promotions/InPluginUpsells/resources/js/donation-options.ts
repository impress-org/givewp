/**
 *
 * @unreleased
 *
 */
declare global {
    interface Window {
        GiveLegacyFormEditor: GiveLegacyFormEditorData;
    }
}

/**
 *
 * @unreleased
 *
 */
export interface GiveLegacyFormEditorData {
    apiRoot: string;
    apiNonce: string;
}

/**
 *
 * @unreleased
 *
 */
const recurringProductRecommendation = document.querySelector('.givewp-donation-options');
const dismissAction = document.querySelector('.givewp-donation-options_close');

/**
 *
 * @unreleased
 *
 */
async function postRequest() {
    const url = `${window.GiveLegacyFormEditor.apiRoot}/admin/recommended-options`;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveLegacyFormEditor.apiNonce,
        },
        body: JSON.stringify({
            option: 'givewp_form_editor_donation_options_recurring_recommendation',
        }),
    });

    const responseData = await response.json();

    if (responseData.success) {
        console.log('Successfully removed option:', responseData);
    }
}

/**
 *
 * @unreleased
 *
 */
dismissAction.addEventListener('click', async function (event) {
    recurringProductRecommendation.remove();
    await postRequest();
});
