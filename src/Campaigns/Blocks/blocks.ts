/**
 * WordPress dependencies
 */
import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import campaignGrid from './CampaignGrid';
import campaignBlock from './Campaign';
import campaignForm from './CampaignForm/resources';

export const getAllBlocks = () => {
    return [
        campaignGrid,
        campaignBlock,
        campaignForm
    ];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema as BlockConfiguration, block.settings);
    }
});

