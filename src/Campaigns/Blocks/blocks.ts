import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';
import * as campaignGoal from './CampaignGoal';

const getAllBlocks = () => {
    return [
        campaignTitleBlock,
        campaignDonateButton,
        campaignGoal,
        campaignCover
    ];
};

getAllBlocks().forEach((block) => {
    block.init();
});
