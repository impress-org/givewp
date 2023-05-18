import {useCallback, useState} from 'react';
import dismissRecommendation from '@givewp/promotions/requests/dismissRecommendation';

type EnumValues =
    | 'givewp_donations_recurring_recommendation_dismissed'
    | 'givewp_donations_fee_recovery_recommendation_dismissed'
    | 'givewp_donations_designated_funds_recommendation_dismissed'
    | 'givewp_reports_recurring_recommendation_dismissed'
    | 'givewp_reports_fee_recovery_recommendation_dismissed'
    | 'givewp_donors_fee_recovery_recommendation_dismissed'
    | 'givewp_reports_fee_recovery_recommendation_dismissed';

export interface RecommendedProductData {
    enum: EnumValues;
    documentationPage: string;
    message: string;
    innerHtml: string;
}

/**
 * @since 2.27.1
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

    const removeRecommendation = async (data: {option: EnumValues}): Promise<void> => {
        setDismissedRecommendations((prev) => [...prev, data.option]);

        await dismissRecommendation(data.option, apiSettings.apiNonce);
    };

    return {getRecommendation, getRandomRecommendation, removeRecommendation};
}
