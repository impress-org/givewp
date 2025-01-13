import * as campaignTitleBlock from './CampaignTitleBlock';

const getAllBlocks = () => {
    return [campaignTitleBlock];
};

getAllBlocks().forEach((block) => {
    block.init();
});
