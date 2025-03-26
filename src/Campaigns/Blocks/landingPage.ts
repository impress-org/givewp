/**
 * WordPress dependencies
 */
import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import campaignDonateButton from './DonateButton';
import campaignDonations from './CampaignDonations';
import campaignDonors from './CampaignDonors';
import campaignGoal from './CampaignGoal';
import campaignStats from './CampaignStats';
import campaignComments from './CampaignComments/resources';

export const getAllBlocks = () => {
    return [
        campaignDonateButton,
        campaignDonations,
        campaignDonors,
        campaignGoal,
        campaignStats,
        campaignComments
    ];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema as BlockConfiguration, block.settings);
    }
});
