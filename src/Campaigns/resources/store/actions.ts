import TYPES from './actionTypes';

const {UPDATE, FETCH_CAMPAIGN} = TYPES;


export const getCampaignById = (id: number) => {
    return {
        type: FETCH_CAMPAIGN,
    };
};

export function updateCampaign(campaign: object) {
    return {
        type: UPDATE,
        campaign,
    };
}
