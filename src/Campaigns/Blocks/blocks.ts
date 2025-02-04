import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';
import * as campaignDonateButton from './DonateButton';
import * as campaignGoal from './CampaignGoal';
import * as campaignList from './CampaignList';

const getAllBlocks = () => {
    return [
        campaignTitleBlock,
        campaignDonateButton,
        campaignGoal,
        campaignCover,
        campaignList
    ];
};

getAllBlocks().forEach((block) => {
    block.init();
});
