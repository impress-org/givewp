import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';

/**
 * A notice above a list table. Product or onboarding content only, never record data.
 *
 * @since TBD
 */
export type ListTableNotice = {
    /** A stable key, for example `campaign-list` or `default-form`. */
    id: string;
    /** Area order: action errors, `system`, `onboarding`, then the recommendation. */
    kind: 'system' | 'onboarding';
    /** The visible heading and the accessible name. */
    title: string;
    message: string;
    link?: {href: string; label: string; opensInNewTab?: boolean};
    /** When present the notice can be dismissed. The screen owns the "shown" flag. */
    onDismiss?: () => void;
};

/**
 * The recommendations a screen offers. Dismissals are stored per site, as before.
 *
 * @since TBD
 */
export type ListTableRecommendations = {
    /** In the screen's order. */
    options: RecommendedProductData[];
    /** Enums of the dismissed options (the site-wide `dismissedRecommendations`). */
    dismissed: string[];
    apiNonce: string;
    /**
     * False when the user cannot store a dismissal (the route needs `manage_options`). The tip then shows
     * without a dismiss button. Defaults to true.
     */
    canDismiss?: boolean;
};
