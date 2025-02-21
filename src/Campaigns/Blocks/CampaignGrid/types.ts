import {TokenItem} from '@wordpress/components/build-types/form-token-field/types';

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
