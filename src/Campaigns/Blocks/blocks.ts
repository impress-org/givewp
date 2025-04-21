/**
 * WordPress dependencies
 */
import {BlockConfiguration, getBlockType, registerBlockType, registerBlockVariation} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import campaignGrid from './CampaignGrid';
import campaignBlock from './Campaign';

export const getAllBlocks = () => {
    return [
        campaignGrid,
        campaignBlock,
    ];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema as BlockConfiguration, block.settings);
    }
});

registerBlockVariation('give/donation-form', {
    name: 'givewp-campaign-donation-form',
    title: 'Campaign Form',
    description: "The GiveWP Campaign Form block inserts an existing campaign form into the page.",
    attributes: {
        campaignId: null,
    },
    isActive: attributes => !!attributes.campaignId,
});
