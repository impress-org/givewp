import {BlockEditProps} from '@wordpress/blocks';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import Stripe from './Stripe';
import {useEffect} from 'react';

export default function PerFormSettings(props: BlockEditProps<any>) {
    const {attributes, setAttributes} = props;
    const {gateways} = getFormBuilderWindowData();

    useEffect(() => {
        if (!attributes.gatewaysSettings) {
            setAttributes({
                gatewaysSettings: {},
            });
        }
    }, []);

    if (!attributes.gatewaysSettings) {
        return null;
    }

    const gatewaysComponents = {
        stripe_payment_element: Stripe,
    };

    const setGatewaySettings = (gatewayId: string) => (settings: any) => {
        setAttributes({
            gatewaysSettings: {
                ...attributes.gatewaysSettings,
                [gatewayId]: {
                    ...attributes.gatewaysSettings[gatewayId],
                    ...settings,
                },
            },
        });
    };

    return (
        <>
            {gateways.map((gateway) => {
                const GatewayComponent = gatewaysComponents[gateway.id];
                if (!GatewayComponent || !gateway.enabled) {
                    return null;
                }

                return (
                    <GatewayComponent
                        key={gateway.id}
                        attributes={attributes.gatewaysSettings[gateway.id] ?? {}}
                        setAttributes={setGatewaySettings(gateway.id)}
                    />
                );
            })}
        </>
    );
}
