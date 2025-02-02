import * as campaignTitleBlock from './CampaignTitleBlock';
import * as campaignCover from './CampaignCover';

const getAllBlocks = () => {
    return [campaignTitleBlock, campaignCover];
};

getAllBlocks().forEach((block) => {
    block.init();
});
