import { useFormState } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import { RecordsSlot } from '../../slots';
import Notice from '@givewp/admin/components/Notices';

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageRecordsTab() {    
    const { isDirty } = useFormState();

    return (
        <>
            {isDirty && (
                <div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
                    <Notice
                        type="info"                        
                    >
                        {__('Some changes made to this subscription will only affect future renewals.', 'give')}                        
                    </Notice>
                </div>
            )}
            <AdminSectionsWrapper>
                <RecordsSlot />
            </AdminSectionsWrapper>
        </>
    );
}
