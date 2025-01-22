import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';
import * as campaignDonors from './CampaignDonors';

const getAllBlocks = () => {
    return [campaignCover, campaignDonateButton, campaignDonors, campaignTitleBlock];
};

getAllBlocks().forEach((block) => {
    block.init();
});
