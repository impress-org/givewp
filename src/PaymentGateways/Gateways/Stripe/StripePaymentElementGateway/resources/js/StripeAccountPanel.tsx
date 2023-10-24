import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Notice, PanelBody, SelectControl, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import './styles.scss';

export default function StripeAccountPanel({
    attributes,
    setAttributes,
}: {
    attributes: StripeProps;
    setAttributes: ({}) => void;
}) {
    const stripeGlobalSettings = window.stripePaymentElementGatewaySettings;

    const textWithLinkToStripeSettings = (textTemplate: string) =>
        createInterpolateElement(textTemplate, {
            a: <a href={stripeGlobalSettings.stripeSettingsUrl} />,
        });

    const hasGlobalDefault = stripeGlobalSettings.defaultAccount;
    const hasPerFormDefault = attributes.stripeAccountId;
    const showGlobalDefaultNotice =
        !hasGlobalDefault &&
        (attributes.stripeUseGlobalDefault || (!attributes.stripeUseGlobalDefault && !hasPerFormDefault));

    const stripeUseGlobalDefaultHelper = textWithLinkToStripeSettings(
        __('All donations are processed through the default account set in the <a>Global settings</a>.', 'give')
    );
    const stripeAccountIdHelper = (() => {
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
        ...Object.keys(stripeGlobalSettings.allAccounts).map((accountId) => ({
            label: stripeGlobalSettings.allAccounts[accountId].account_name,
            value: stripeGlobalSettings.allAccounts[accountId].account_id,
        })),
    ];

    return (
        <InspectorControls>
            <PanelBody
                title={__('Stripe Account', 'give')}
                className="givewp-stripe-payment-element__panel"
                initialOpen={true}
            >
                <ToggleControl
                    label={__('Use global default', 'give')}
                    checked={attributes.stripeUseGlobalDefault}
                    onChange={(value) => setAttributes({stripeUseGlobalDefault: value})}
                    help={stripeUseGlobalDefaultHelper}
                />
                {!attributes.stripeUseGlobalDefault && (
                    <SelectControl
                        label={__('Choose Account', 'give')}
                        value={attributes.stripeAccountId}
                        options={selectAccountOptions}
                        onChange={(value: string) => setAttributes({stripeAccountId: value})}
                        help={stripeAccountIdHelper}
                    />
                )}
                {showGlobalDefaultNotice && (
                    <Notice
                        isDismissible={false}
                        status="warning"
                        actions={[
                            {
                                label: __('Connect a Stripe account', 'give'),
                                url: stripeGlobalSettings.stripeSettingsUrl,
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
    stripeUseGlobalDefault: boolean;
    stripeAccountId: string;
};

type StripeGlobalSettingsProps = {
    defaultAccount: string;
    allAccounts: {
        [key: string]: {
            account_id: string;
            account_name: string;
        };
    };
    stripeSettingsUrl: string;
};

declare const window: {
    stripePaymentElementGatewaySettings: StripeGlobalSettingsProps;
} & Window;
