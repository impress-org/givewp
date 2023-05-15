import {useCallback, useState} from 'react';

type EnumValues =
    | 'givewp_donations_recurring_recommendation_dismissed'
    | 'givewp_donations_fee_recovery_recommendation_dismissed'
    | 'givewp_donations_designated_funds_recommendation_dismissed'
    | 'givewp_reports_recurring_recommendation_dismissed'
    | 'givewp_reports_fee_recovery_recommendation_dismissed'
    | 'givewp_donors_fee_recovery_recommendation_dismissed';

export interface RecommendedProductData {
    enum: EnumValues;
    documentationPage: string;
    message: string;
    innerHtml: string;
}

/**
 * @unreleased
 */
export function useRecommendations(apiSettings, options) {
    const [dismissedRecommendations, setDismissedRecommendations] = useState<string[]>(
        apiSettings.dismissedRecommendations
    );
    const getRandomRecommendation = useCallback((): RecommendedProductData | null => {
        const availableOptions = options.filter((option) => !dismissedRecommendations.includes(option.enum));

        if (availableOptions.length === 0) {
            return null;
        }

        const randomIndex = Math.floor(Math.random() * availableOptions.length);

        return availableOptions[randomIndex];
    }, [dismissedRecommendations]);

    const getRecommendation = useCallback((): RecommendedProductData | null => {
        const availableOptions = options.filter((option) => !dismissedRecommendations.includes(option.enum));

        if (availableOptions.length === 0) {
            return null;
        }

        return availableOptions[0];
    }, [dismissedRecommendations]);

    const removeRecommendation = async (data: { option: EnumValues }): Promise<void> => {
        const url = `/wp-json/give-api/v2/admin/recommended-options`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiSettings.apiNonce,
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

    return {getRecommendation, getRandomRecommendation, removeRecommendation};
}
