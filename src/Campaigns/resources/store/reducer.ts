import TYPES from './actionTypes';

import {getGiveCampaignDetailsWindowData} from '../admin/components/CampaignDetailsPage';

const {GET, UPDATE} = TYPES;

export default (state = getGiveCampaignDetailsWindowData().campaign.properties, action) => {

    switch (action.type) {
        case UPDATE:

            console.log('updating', action)
            return {
                ...state,
                ...action.campaign
            };
    }

    return state;
}


