import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignDonateButton from './DonateButton';

const getAllBlocks = () => {
    return [
        campaignTitleBlock,
        campaignDonateButton
    ];
};

getAllBlocks().forEach((block) => {
    block.init();
});
