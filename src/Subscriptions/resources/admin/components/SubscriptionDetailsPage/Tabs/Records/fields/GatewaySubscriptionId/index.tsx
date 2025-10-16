import LockedTextInput from '@givewp/admin/fields/LockedTextInput';
import {__} from '@wordpress/i18n';

/**
 * @since 4.11.0
 */
export default function GatewaySubscriptionId() {
    return (
        <LockedTextInput
            name="gatewaySubscriptionId"
            label={__('Gateway Subscription ID', 'give')}
            description={__(
                'Connects the subscription from the gateway to Give, syncing subscription changes and recording renewals.',
                'give'
            )}
            placeholder={__('Enter gateway subscription ID', 'give')}
            warningMessage={__(
                'Changing the Gateway Subscription ID will stop renewal recordings in Give if not accurate.',
                'give'
            )}
        />
    );
}
