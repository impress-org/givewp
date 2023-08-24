import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Notice, PanelBody, SelectControl, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useEffect} from 'react';

const MOCK = {
    default: null,
    accounts: {
        '1': __('Account 1', 'give'),
        '2': __('Account 2', 'give'),
    },
};

export default function Stripe({attributes, setAttributes}: {attributes: StripeProps; setAttributes: ({}) => void}) {
    useEffect(() => {
        if (Object.keys(attributes).length === 0) {
            setAttributes({
                useGlobalDefault: true,
                accountId: '',
            });
        }
    }, []);

    const {gatewaySettingsUrl} = getFormBuilderWindowData();

    const textWithLinkToStripeSettings = (textTemplate: string) =>
        createInterpolateElement(textTemplate, {
            a: <a href={`${gatewaySettingsUrl}&section=stripe-settings&group=accounts`} />,
        });

    const hasGlobalDefault = MOCK.default;
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
        ...Object.keys(MOCK.accounts).map((accountId) => ({
            label: MOCK.accounts[accountId],
            value: accountId,
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
