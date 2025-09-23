import Notice from '@givewp/admin/components/Notices';
import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import { __ } from '@wordpress/i18n';
import { useFormState } from 'react-hook-form';
import { RecordsSlot } from '../../slots';
import SubscriptionDetails from './SubscriptionDetails';

/**
 * @unreleased
 */
const AdminNotice = ({ isVisible, children }: {isVisible: boolean, children: React.ReactNode}) => {
    if (!isVisible) return null;

    return (
        <div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
            <Notice type="info">{children}</Notice>
        </div>
    );
};

/**
 * @unreleased add SubscriptionDetails
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageRecordsTab() {
    const { isDirty, dirtyFields } = useFormState();

    const isSubscriptionStatusModified = isDirty && Boolean(dirtyFields?.status);
    const totalDirtyFieldsCount = Object.keys(dirtyFields || {}).length;
    const hasNonStatusFieldChanges = isDirty && totalDirtyFieldsCount > (isSubscriptionStatusModified ? 1 : 0);

    return (
        <>
            <AdminNotice isVisible={isSubscriptionStatusModified}>
                {__('Changing the status here will not update the status at the payment gateway.', 'give')}
            </AdminNotice>
            <AdminNotice isVisible={hasNonStatusFieldChanges}>
                {__('Some changes made to this subscription will only affect future renewals.', 'give')}
            </AdminNotice>

            <AdminSectionsWrapper>
                <SubscriptionDetails />
                <RecordsSlot />
            </AdminSectionsWrapper>
        </>
    );
}
