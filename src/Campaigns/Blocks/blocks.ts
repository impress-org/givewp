/**
 * WordPress dependencies
 */
import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import campaignCover from './CampaignCover';
import campaignDonateButton from './DonateButton';
import campaignDonations from './CampaignDonations';
import campaignDonors from './CampaignDonors';
import campaignTitle from './CampaignTitle';
import campaignStats from './CampaignStats';
import campaignGoal from './CampaignGoal';
import campaignList from './CampaignList';
import campaignBlock from './Campaign';

export const getAllBlocks = () => {
    return [
        campaignCover,
        campaignDonateButton,
        campaignDonations,
        campaignDonors,
        campaignTitle,
        campaignGoal,
        campaignList,
        campaignBlock,
        campaignStats
    ];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema as BlockConfiguration, block.settings);
    }
});
