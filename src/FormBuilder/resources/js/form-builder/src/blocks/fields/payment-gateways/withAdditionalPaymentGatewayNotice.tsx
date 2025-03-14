import {createHigherOrderComponent} from "@wordpress/compose";
import {PanelRow} from "@wordpress/components";
import InspectorNotice from "@givewp/form-builder/components/settings/InspectorNotice";
import {__} from "@wordpress/i18n";
import {InspectorControls} from "@wordpress/block-editor";
import {useState} from "react";

declare const window: {
    additionalPaymentGatewaysNotificationData: {
        actionUrl: string;
        isDismissed: boolean;
    };
} & Window;

const AdditionalPaymentGatewaysNotice = () => {
    const {actionUrl, isDismissed} = window.additionalPaymentGatewaysNotificationData;
    const [showNotification, setShowNotification] = useState(!window.additionalPaymentGatewaysNotificationData.isDismissed);
    const onDismissNotification = () => fetch(actionUrl, {method: 'POST'}).then(() => setShowNotification(false))

    return (
        <InspectorControls>
            {showNotification && (
                <PanelRow>
                    <InspectorNotice
                        title={__('Additional Payment Gateways', 'give')}
                        description={__('Enable multiple payment gateways on your forms via the global settings.', 'give')}
                        helpText={__('Go to payment gateway settings', 'give')}
                        helpUrl={'/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=gateways-settings&group=v3'}
                        onDismiss={onDismissNotification}
                    />
                </PanelRow>
            )}
        </InspectorControls>
    )
}

const withAdditionalPaymentGatewayNotice = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name === 'givewp/payment-gateways') {
            return (
                <>
                    <BlockEdit {...props} />
                    <AdditionalPaymentGatewaysNotice {...props} />
                </>
            );
        }
        return <BlockEdit {...props} />;
    };
}, 'withAdditionalPaymentGatewayNotice');

export default withAdditionalPaymentGatewayNotice;
