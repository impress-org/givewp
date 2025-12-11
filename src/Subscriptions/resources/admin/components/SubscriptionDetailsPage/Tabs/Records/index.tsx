import Notice from '@givewp/admin/components/Notices';
import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import { __ } from '@wordpress/i18n';
import { useFormState } from 'react-hook-form';
import { RecordsSlot } from '../../slots';
import SubscriptionDetails from './SubscriptionDetails';

/**
 * @since 4.10.0 add SubscriptionDetails
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageRecordsTab() {
    const {isDirty, dirtyFields} = useFormState();

    const isStatusDirty = isDirty && Boolean(dirtyFields?.status);
    const totalDirtyFieldsCount = Object.keys(dirtyFields || {}).length;
    const hasNonStatusFieldChanges = isDirty && totalDirtyFieldsCount > (isStatusDirty ? 1 : 0);

    return (
        <>
            {hasNonStatusFieldChanges && (
                <div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
                    <Notice type="info">
                        {__('Some changes made to this subscription will only affect future renewals.', 'give')}
                    </Notice>
                </div>
            )}
            <AdminSectionsWrapper>
                <SubscriptionDetails />
                <RecordsSlot/>
            </AdminSectionsWrapper>
        </>
    );
}
