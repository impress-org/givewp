import { MouseEventHandler } from 'react';

export interface TokenItem {
    /**
     *  The value of the token.
     */
    value: string;
    /**
     * One of 'error', 'validating', or 'success'. Applies styles to token.
     */
    status?: 'error' | 'success' | 'validating';
    /**
     * If not falsey, will add a title to the token.
     */
    title?: string;
    /**
     * When true, renders tokens as without a background.
     */
    isBorderless?: boolean;
    /**
     * Function to call when onMouseEnter event triggered on token.
     */
    onMouseEnter?: MouseEventHandler<HTMLSpanElement>;
    /**
     *  Function to call when onMouseLeave is triggered on token.
     */
    onMouseLeave?: MouseEventHandler<HTMLSpanElement>;
}

export type CampaignGridType = {
    layout: string;
    showImage: boolean;
    showDescription: boolean;
    showGoal: boolean;
    showPagination: boolean;
    sortBy: string;
    orderBy: string;
    filterBy: (string | TokenItem)[];
    perPage: number;
}
