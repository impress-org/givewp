/**
 * WordPress dependencies
 */
import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import campaignCover from './CampaignCover';
import campaignDonateButton from './DonateButton';
import campaignDonors from './CampaignDonors';
import campaignTitle from './CampaignTitle';

export const getAllBlocks = () => {
    return [campaignCover, campaignDonateButton, campaignDonors, campaignTitle];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema as BlockConfiguration, block.settings);
    }
});
