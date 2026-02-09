/**
 * WordPress dependencies
 */
import { getBlockType, registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import donationFormBlock from './DonationFormBlock';

export const getAllBlocks = () => {
    return [donationFormBlock];
};

getAllBlocks().forEach((block) => {
    if (!getBlockType(block.schema.name)) {
        registerBlockType(block.schema.name, {
            ...block.schema,
            ...block.settings,
        } as Parameters<typeof registerBlockType>[1]);
    }
});
