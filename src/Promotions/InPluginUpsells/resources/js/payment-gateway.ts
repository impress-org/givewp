import dismissRecommendation from '@givewp/promotions/requests/dismissRecommendation';

/**
 *
 * @since 2.27.1
 *
 */
declare global {
    interface Window {
        GiveSettings: GiveSettingsData;
    }
}

export interface GiveSettingsData {
    apiRoot: string;
    apiNonce: string;
}

/**
 *
 * @since 2.27.1
 *
 */
const feeRecoveryProductRecommendation = document.querySelector(
    '.givewp-payment-gateway-fee-recovery-recommendation-row'
);

if (feeRecoveryProductRecommendation) {
    const dismissAction = document.querySelector('.givewp-payment-gateway-fee-recovery-recommendation_close');
    const table = document.querySelector('.give-setting-tab-body-gateways');
    const preceedingContent = table.querySelector('tr');

    preceedingContent.insertAdjacentElement('afterend', feeRecoveryProductRecommendation);

    dismissAction.addEventListener('click', async function (event) {
        feeRecoveryProductRecommendation.remove();
        await dismissRecommendation('givewp_payment_gateway_fee_recovery_recommendation', window.GiveSettings.apiNonce);
    });
}
