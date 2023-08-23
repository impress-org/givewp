import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Notice, PanelBody, ToggleControl} from '@wordpress/components';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const MOCK = {
    default: null,
    accounts: {
        1: __('Account 1', 'give'),
        2: __('Account 2', 'give'),
    },
};

export default function StripeAccounts({attributes: {stripeAccounts}, setAttributes}) {
    const {gatewaySettingsUrl} = getFormBuilderWindowData();

    const handleSetAttributes = (newAttributes: any) => {
        setAttributes({
            stripeAccounts: {
                ...stripeAccounts,
                ...newAttributes,
            },
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
                {!MOCK.default && (
                    <Notice
                        isDismissible={false}
                        status="warning"
                        actions={[
                            {
                                label: __('Connect a Stripe account', 'give'),
                                url: `${gatewaySettingsUrl}&section=stripe-settings&group=accounts`,
                                variant: 'link',
                            },
                        ]}
                    >
                        <h4>{__('No default account set', 'give')}</h4>
                        <p>{__('All donations are processed through the default Stripe account.', 'give')}</p>
                    </Notice>
                )}
            </PanelBody>
        </InspectorControls>
    );
}
