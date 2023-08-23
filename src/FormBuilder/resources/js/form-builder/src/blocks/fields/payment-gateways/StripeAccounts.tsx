import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {PanelBody, ToggleControl} from '@wordpress/components';

const MOCK = {
    default: null,
    accounts: {
        1: __('Account 1', 'give'),
        2: __('Account 2', 'give'),
    },
};
export default function StripeAccounts({attributes, setAttributes, gateways}) {
    const {stripeAccounts} = attributes;
    const handleSetAttributes = (newAttributes: any) => {
        setAttributes({
            stripeAccounts: {
                ...stripeAccounts,
                ...newAttributes
            }
        });
    };

    return (
        <InspectorControls>
            <PanelBody title={__('Stripe Account', 'give')} initialOpen={true}>
                <ToggleControl
                    label={__('Use global default', 'give')}
                    checked={stripeAccounts.useGlobalDefault}
                    onChange={(value) => handleSetAttributes({useGlobalDefault: value})}
                />
            </PanelBody>
        </InspectorControls>
    );
}
