import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";
import { useFormState } from "react-hook-form";
import { __ } from "@wordpress/i18n";
import AdminSection, { AdminSectionField } from "@givewp/components/AdminDetailsPage/AdminSection";
import StatusField from "@givewp/admin/fields/Status";

const { subscriptionStatuses } = getSubscriptionOptionsWindowData();

/**
 * @since 4.10.0
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
        </AdminSection>
    );
}
