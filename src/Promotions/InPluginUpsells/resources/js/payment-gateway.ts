/**
 *
 * @unreleased
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
 * @unreleased
 *
 */
const feeRecoveryProductRecommendation = document.querySelector(
    '.givewp-payment-gateway-fee-recovery-recommendation-row'
);
const dismissAction = document.querySelector('.givewp-payment-gateway-fee-recovery-recommendation_close');
const table = document.querySelector('.give-setting-tab-body-gateways');
const preceedingContent = table.querySelector('tr');

preceedingContent.insertAdjacentElement('afterend', feeRecoveryProductRecommendation);

if (feeRecoveryProductRecommendation) {
    dismissAction.addEventListener('click', async function (event) {
        feeRecoveryProductRecommendation.remove();
        await postRequest();
    });
}

/**
 *
 * @unreleased
 *
 */
async function postRequest() {
    const url = `${window.GiveSettings.apiRoot}/admin/recommended-options`;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveSettings.apiNonce,
        },
        body: JSON.stringify({
            option: 'givewp_payment_gateway_fee_recovery_recommendation',
        }),
    });

    const responseData = await response.json();

    if (responseData.success) {
        console.log('Successfully removed option:', responseData);
    }
}
