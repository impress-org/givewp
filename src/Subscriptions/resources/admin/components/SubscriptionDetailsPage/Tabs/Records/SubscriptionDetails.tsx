import AssociatedDonorField from '@givewp/admin/fields/AssociatedDonor';
import StatusField from '@givewp/admin/fields/Status';
import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {getSubscriptionOptionsWindowData} from '@givewp/subscriptions/utils';
import {__} from '@wordpress/i18n';
import {useFormState} from 'react-hook-form';
import GatewaySubscriptionId from './fields/GatewaySubscriptionId';

const {subscriptionStatuses, mode} = getSubscriptionOptionsWindowData();

/**
 * @since 4.10.0
 */
export default function SubscriptionDetails() {
    const {errors} = useFormState();

    return (
        <>
            <AdminSection
                title={__('Subscription details', 'give')}
                description={__('This includes the subscription information', 'give')}
            >
                <AdminSectionField error={errors.status?.message as string}>
                    <StatusField statusOptions={subscriptionStatuses} />
                </AdminSectionField>
                <GatewaySubscriptionId />
            </AdminSection>
            <AdminSection
                title={__('Associated donor', 'give')}
                description={__('Manage the donor connected to this subscription', 'give')}
            >
                <AssociatedDonorField
                    name="donorId"
                    mode={mode}
                    label={__('Donor', 'give')}
                    description={__('Link the subscription to the selected donor', 'give')}
                />
            </AdminSection>
        </>
    );
}
