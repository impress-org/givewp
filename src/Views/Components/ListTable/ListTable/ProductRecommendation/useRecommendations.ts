import {__} from '@wordpress/i18n';

type EnumValues =
    | 'givewp_recurring_recommendation_dismissed'
    | 'givewp_fee_recovery_recommendation_dismissed'
    | 'givewp_designated_funds_recommendation_dismissed';

export interface recommendedProductData {
    enum: EnumValues;
    documentationPage: string;
    message: string;
    innerHtml: string;
}

interface recommendedProducts {
    recurring: recommendedProductData
    feeRecovery: recommendedProductData
    designatedFunds: recommendedProductData
}

const recommendedProducts: recommendedProducts = {
    // ToDo: Use UTM links for documentationPage
    recurring: {
        enum: 'givewp_recurring_recommendation_dismissed',
        documentationPage: '',
        message: 'Increase your fundraising revenue by over 30% with recurring giving campaigns.',
        innerHtml: __('Get More Donations', 'give'),
    },
    feeRecovery: {
        enum: 'givewp_fee_recovery_recommendation_dismissed',
        documentationPage: '',
        message:
            'Maximize your total donated income to 100% by providing donors with the option to cover the credit card processing fees.',
        innerHtml: __('Get More Donations', 'give'),
    },
    designatedFunds: {
        enum: 'givewp_designated_funds_recommendation_dismissed',
        documentationPage: ' ',
        message:
            'Elevate your fundraising campaigns with multiple forms, unlimited donation funds, and tailored fundraising reports.',
        innerHtml: __('Start creating designated funds', 'give'),
    },
};

export function useRecommendations() {
    const getRecommendation = () => {
        const options = [recommendedProducts.recurring, recommendedProducts.feeRecovery, recommendedProducts.designatedFunds];
        const dismissedOptions = window.GiveDonations.dismissedRecommendations || [];

        const availableOptions = options.filter(option => !dismissedOptions.includes(option.enum));

        if (availableOptions.length === 0) {
            return null;
        }
console.log(availableOptions)
        const randomIndex = Math.floor(Math.random() * availableOptions.length);
        return availableOptions[randomIndex];
    }


    const removeRecommendation = async (data) => {
            const url = `/wp-json/give-api/v2/admin/recommended-options`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.GiveDonations.apiNonce,
                    },
                    body: JSON.stringify(data),
                });

                return await response.json();
            } catch (error) {
                console.error(error);
            }
        }


    return { getRecommendation, removeRecommendation };
}
