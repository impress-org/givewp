import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Notice, PanelBody, SelectControl, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useEffect} from 'react';

export default function Stripe({attributes, setAttributes}: {attributes: StripeProps; setAttributes: ({}) => void}) {
    useEffect(() => {
        if (Object.keys(attributes).length === 0) {
            setAttributes({
                useGlobalDefault: true,
                accountId: '',
            });
        }
    }, []);

    const {gatewaysGlobalSettings, gatewaySettingsUrl} = getFormBuilderWindowData();
    const stripeGlobalSettings: StripeGlobalSettingsProps = gatewaysGlobalSettings?.stripe_payment_element ?? {};

    const textWithLinkToStripeSettings = (textTemplate: string) =>
        createInterpolateElement(textTemplate, {
            a: <a href={`${gatewaySettingsUrl}&section=stripe-settings&group=accounts`} />,
        });

    const hasGlobalDefault = stripeGlobalSettings.default;
    const hasPerFormDefault = attributes.accountId;
    const showGlobalDefaultNotice =
        (attributes.useGlobalDefault && !hasGlobalDefault) || (!attributes.useGlobalDefault && !hasPerFormDefault);

    const useGlobalDefaultHelper = textWithLinkToStripeSettings(
        __('All donations are processed through the default account set in the <a>Global settings</a>.', 'give')
    );
    const selectAccountHelper = (() => {
        const sharedText = __('Select an account you would like to use to process donations for this form.', 'give');

        if (showGlobalDefaultNotice) {
            return <>{sharedText}</>;
        }

        return textWithLinkToStripeSettings(
            sharedText + ' ' + __('You can also add another account in <a>Global settings</a>.', 'give')
        );
    })();

    const selectAccountOptions = [
        {label: __('Select', 'give'), value: ''},
        ...Object.keys(stripeGlobalSettings.accounts).map((accountId) => ({
            label: stripeGlobalSettings.accounts[accountId].account_name,
            value: stripeGlobalSettings.accounts[accountId].account_id,
        })),
    ];

    return (
        <InspectorControls>
            <PanelBody title={__('Stripe Account', 'give')} initialOpen={true}>
                <ToggleControl
                    label={__('Use global default', 'give')}
                    checked={attributes.useGlobalDefault}
                    onChange={(value) => setAttributes({useGlobalDefault: value})}
                    help={useGlobalDefaultHelper}
                />
                {!attributes.useGlobalDefault && (
                    <SelectControl
                        label={__('Choose Account', 'give')}
                        value={attributes.accountId}
                        options={selectAccountOptions}
                        onChange={(value: string) => setAttributes({accountId: value})}
                        help={selectAccountHelper}
                    />
                )}
                {showGlobalDefaultNotice && (
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

type StripeProps = {
    useGlobalDefault: boolean;
    accountId?: string;
};

type StripeGlobalSettingsProps = {
    default: string;
    accounts: {
        [key: string]: {
            account_id: string;
            account_name: string;
        };
    };
};
