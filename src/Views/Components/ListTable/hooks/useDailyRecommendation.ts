import {useCallback, useState} from 'react';
import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import dismissRecommendation from '@givewp/promotions/requests/dismissRecommendation';
import {siteToday} from '../filters/siteDate';
import {pickDailyRecommendation} from '../notices/dailyRecommendation';
import type {ListTableRecommendations} from '../notices/types';

/**
 * Today's recommendation for a screen. It is picked once, when the page loads, and stays fixed for
 * the page's life. Dismissing hides it at once and stores the dismissal; no other one appears until
 * the next load. If the dismissal cannot be saved, the same recommendation shows again. A user who
 * cannot dismiss (`canDismiss: false`) gets no dismiss function, so the button is not shown.
 *
 * @since TBD
 */
export default function useDailyRecommendation(config?: ListTableRecommendations): {
    recommendation: RecommendedProductData | null;
    /** Undefined when the user cannot dismiss, so the tip shows without a dismiss button. */
    dismiss?: () => void;
} {
    const [picked] = useState<RecommendedProductData | null>(() =>
        config ? pickDailyRecommendation(config.options, config.dismissed, siteToday()) : null
    );
    const [isDismissed, setIsDismissed] = useState(false);

    const dismiss = useCallback(() => {
        if (!picked || !config) {
            return;
        }

        setIsDismissed(true);
        void dismissRecommendation(picked.enum, config.apiNonce).then((saved) => {
            // The dismissal was not saved, so the tip would come back on the next load. Show it again now.
            if (!saved) {
                setIsDismissed(false);
            }
        });
    }, [picked, config]);

    return {
        recommendation: isDismissed ? null : picked,
        dismiss: config?.canDismiss === false ? undefined : dismiss,
    };
}
