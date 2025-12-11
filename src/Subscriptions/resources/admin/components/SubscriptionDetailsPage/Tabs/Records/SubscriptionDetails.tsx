/**
 * External dependencies
 */
import { useFormState } from 'react-hook-form';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import AssociatedDonorField from '@givewp/admin/fields/AssociatedDonor';
import CampaignFormField from "@givewp/admin/fields/CampaignFormGroup";
import StatusField from '@givewp/admin/fields/Status';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { getSubscriptionOptionsWindowData } from '@givewp/subscriptions/utils';
import GatewaySubscriptionId from './fields/GatewaySubscriptionId';

const { subscriptionStatuses, mode } = getSubscriptionOptionsWindowData();

/**
 * @since 4.11.0 Added Campaign, Form, and Gateway Subscription ID fields
 * @since 4.10.0
 */
export default function SubscriptionDetails() {
    const { errors } = useFormState();

    return (
        <>
            <AdminSection
                title={__('Subscription details', 'give')}
                description={__('This includes the subscription information', 'give')}
            >
                <AdminSectionField error={errors.status?.message as string}>
                    <StatusField statusOptions={subscriptionStatuses} />
                    <CampaignFormField
                        campaignIdFieldName="campaignId"
                        formIdFieldName="donationFormId"
                    />
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
