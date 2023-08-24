import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Notice, PanelBody, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const MOCK = {
    default: null,
    accounts: {
        1: __('Account 1', 'give'),
        2: __('Account 2', 'give'),
    },
};

export default function StripeAccount({attributes, setAttributes}) {
    const {stripeAccount}: {stripeAccount: StripeAccountProps} = attributes;
    const {gatewaySettingsUrl} = getFormBuilderWindowData();

    const handleSetAttributes = (newAttributes: any) => {
        setAttributes({
            stripeAccount: {
                ...stripeAccount,
                ...newAttributes,
            },
        });
    };

    const hasGlobalDefault = MOCK.default;
    const useGlobalDefaultHelper = createInterpolateElement(
        __('All donations are processed through the default account set in the <a>Global settings</a>.', 'give'),
        {
            a: <a href={`${gatewaySettingsUrl}&section=stripe-settings&group=accounts`} />,
        }
    );

    return (
        <InspectorControls>
            <PanelBody title={__('Stripe Account', 'give')} initialOpen={true}>
                <ToggleControl
                    label={__('Use global default', 'give')}
                    checked={stripeAccount.useGlobalDefault}
                    onChange={(value) => handleSetAttributes({useGlobalDefault: value})}
                    help={hasGlobalDefault && useGlobalDefaultHelper}
                />
                {!hasGlobalDefault && (
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

type StripeAccountProps = {
    useGlobalDefault: boolean;
    accountId?: number;
};
