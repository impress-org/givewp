import {addFilter} from '@wordpress/hooks';

import addOfflineAttributes from './addOfflineAttributes';
import withOfflineInspectorControls from "./withOfflineInspectorControls";

declare const window: {
    giveOfflineGatewaySettings: {
        offlineEnabled: string;
    };
} & Window;

addFilter(
    'blocks.registerBlockType',
    'givewp/stripe-payment-element',
    addOfflineAttributes
);

if (window.giveOfflineGatewaySettings.offlineEnabled) {
    addFilter('editor.BlockEdit', 'givewp/stripe-payment-element', withOfflineInspectorControls);
}
