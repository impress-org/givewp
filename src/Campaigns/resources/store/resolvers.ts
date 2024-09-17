import {setCampaign, getCampaignById as fetchCampaign} from './actions';

export function * getCampaignById(id: number) {
    const campaign = yield fetchCampaign(id);

    return setCampaign(campaign);
}
