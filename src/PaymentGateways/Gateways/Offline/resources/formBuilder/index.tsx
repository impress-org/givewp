import {addFilter} from '@wordpress/hooks';

import addOfflineAttributes from './addOfflineAttributes';
import withOfflineInspectorControls from "./withOfflineInspectorControls";

addFilter(
    'blocks.registerBlockType',
    'givewp/stripe-payment-element',
    addOfflineAttributes
);

addFilter(
    'editor.BlockEdit',
    'givewp/stripe-payment-element',
    withOfflineInspectorControls
);
