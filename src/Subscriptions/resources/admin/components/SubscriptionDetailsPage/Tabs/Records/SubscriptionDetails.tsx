import StatusField from '@givewp/admin/fields/Status';
import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {getSubscriptionOptionsWindowData} from '@givewp/subscriptions/utils';
import {__} from '@wordpress/i18n';
import {useFormState} from 'react-hook-form';
import GatewaySubscriptionId from './fields/GatewaySubscriptionId';

const {subscriptionStatuses} = getSubscriptionOptionsWindowData();

/**
 * @unreleased
 */
export default function SubscriptionDetails() {
    const {errors} = useFormState();

    return (
        <AdminSection
            title={__('Subscription details', 'give')}
            description={__('This includes the subscription information', 'give')}
        >
            <AdminSectionField error={errors.status?.message as string}>
                <StatusField statusOptions={subscriptionStatuses} />
            </AdminSectionField>
            <GatewaySubscriptionId />
        </AdminSection>
    );
}
