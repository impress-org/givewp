import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';
import * as campaignDonors from './CampaignDonors';
import * as campaignTitle from './CampaignTitle';

const getAllBlocks = () => {
    return [campaignCover, campaignDonateButton, campaignDonors, campaignTitle];
};

getAllBlocks().forEach((block) => {
    block.init();
});
