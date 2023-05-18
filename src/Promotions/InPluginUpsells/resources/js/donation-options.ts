import dismissRecommendation from '@givewp/promotions/requests/dismissRecommendation';

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
const preceedingContent = document.querySelector('._give_custom_amount_text_field');

if (recurringProductRecommendation && preceedingContent) {
    preceedingContent.insertAdjacentElement('afterend', recurringProductRecommendation);

    dismissAction.addEventListener('click', async function (event) {
        recurringProductRecommendation.remove();
        await dismissRecommendation(
            'givewp_form_editor_donation_options_recurring_recommendation',
            window.GiveLegacyFormEditor.apiRoot,
            window.GiveLegacyFormEditor.apiNonce
        );
    });
}
