import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';

const getAllBlocks = () => {
    return [
        campaignTitleBlock,
        campaignDonateButton,
        campaignCover
    ];
};

getAllBlocks().forEach((block) => {
    block.init();
});
