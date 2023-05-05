import {__} from '@wordpress/i18n';
import {useState} from 'react';

type EnumValues =
    | 'givewp_recurring_recommendation_dismissed'
    | 'givewp_fee_recovery_recommendation_dismissed'
    | 'givewp_designated_funds_recommendation_dismissed';

export interface RecommendedProductData {
    enum: EnumValues;
    documentationPage: string;
    message: string;
    innerHtml: string;
}

interface RecommendedProducts {
    recurring: RecommendedProductData;
    feeRecovery: RecommendedProductData;
    designatedFunds: RecommendedProductData;
}

const recommendedProducts: RecommendedProducts = {
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

/**
 * @unreleased
 */
export function useRecommendations() {
    const [dismissedRecommendations, setDismissedRecommendations] = useState<string[]>(
        window.GiveDonations.dismissedRecommendations
    );
    const getRecommendation = (): RecommendedProductData | null => {
        const options = [
            recommendedProducts.recurring,
            recommendedProducts.feeRecovery,
            recommendedProducts.designatedFunds,
        ];

        const availableOptions = options.filter((option) => !dismissedRecommendations.includes(option.enum));

        if (availableOptions.length === 0) {
            return null;
        }

        const randomIndex = Math.floor(Math.random() * availableOptions.length);
        return availableOptions[randomIndex];
    };
    
    const removeRecommendation = async (data: {option: EnumValues}): Promise<void> => {
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

            const result = await response.json();
            if (result.success) {
                setDismissedRecommendations((prev) => [...prev, data.option]);
            }
        } catch (error) {
            console.error(error);
        }
    };

    return {getRecommendation, removeRecommendation};
}
