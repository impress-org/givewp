import { Children } from 'react';
import Notice from '@givewp/admin/components/Notices';
import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import { __ } from '@wordpress/i18n';
import { useFormState } from 'react-hook-form';
import { RecordsSlot } from '../../slots';
import { NotesIcon } from '@givewp/admin/components/PrivateNotes/Icons';
import styles from './styles.module.scss';

/**
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageRecordsTab() {
    const { isDirty } = useFormState();

    return (
        <>
            {isDirty && (
                <div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
                    <Notice type="info">
                        {__('Some changes made to this subscription will only affect future renewals.', 'give')}
                    </Notice>
                </div>
            )}
            <AdminSectionsWrapper>
                <RecordsSlot>
                    {(fills) => {
                        if (!fills || Children.count(fills) === 0) {
                            return (
                                <div className={styles.emptyState}>
                                    <NotesIcon />
                                    <p className={styles.description}>{__('No records found', 'give')}</p>
                                </div>
                            );
                        }

                        return fills;
                    }}
                </RecordsSlot>
            </AdminSectionsWrapper>
        </>
    );
}
