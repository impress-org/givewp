import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignDonateButton from './DonateButton';
import * as campaignGoal from './CampaignGoal';

const getAllBlocks = () => {
    return [
        campaignTitleBlock,
        campaignDonateButton,
        campaignGoal,
    ];
};

getAllBlocks().forEach((block) => {
    block.init();
});
