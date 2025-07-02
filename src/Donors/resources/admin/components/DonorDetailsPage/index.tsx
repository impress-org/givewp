/**
 * External Dependencies
 */
import { useState } from 'react';
import cx from 'classnames';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal Dependencies
*/
import { TrashIcon, ViewIcon } from '@givewp/components/AdminDetailsPage/Icons';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import { getDonorOptionsWindowData, useDonorEntityRecord } from '@givewp/donors/utils';
import styles from './DonorDetailsPage.module.scss';
import tabDefinitions from './Tabs/definitions';

/**
 * @since 4.4.0
 */
const StatusBadge = ({ status }: { status: string }) => {
    const statusMap = {
        current: __('Current', 'give'),
        prospective: __('Prospective', 'give'),
        retained: __('Retained', 'give'),
        lapsed: __('Lapsed', 'give'),
        new: __('New', 'give'),
        recaptured: __('Recaptured', 'give'),
        recurring: __('Recurring', 'give'),
    };

    if (!statusMap[status]) {
        return null;
    }

    return (
        <div className={`${styles.statusBadge} ${styles[`statusBadge--${status}`]}`}>
            <span className={styles.statusBadgeIcon}>{statusMap[status].substring(0, 1)}</span>
            <span className={styles.statusBadgeText}>{statusMap[status]}</span>
        </div>
    );
};

/**
 * @since 4.4.0
 */
export default function DonorDetailsPage() {
    const { adminUrl } = getDonorOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<boolean>(false);

    const { record: donor } = useDonorEntityRecord();

    const SendEmailButton = ({ className }: { className: string }) => {
        if (!donor?.email) {
            return null;
        }

        return (
            <a
                href={`mailto:${donor.email}`}
                className={className}
                aria-label={__('Send email to donor', 'give')}
            >
                {__('Send Email', 'give')}
            </a>
        );
    };

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                {donor?.wpUserPermalink && (
                    <a
                        href={donor.wpUserPermalink}
                        target="_blank"
                        aria-label={__('View WordPress profile', 'give')}
                        className={className}
                    >
                        <ViewIcon /> {__('View WordPress profile', 'give')}
                    </a>
                )}

                {/* <a
                    href="#"
                    className={cx(className, styles.archive)}
                    onClick={() => setShowConfirmationDialog(true)}
                >
                    <TrashIcon /> {__('Delete Donor', 'give')}
                </a> */}
            </>
        );
    };

    return (
        <AdminDetailsPage
            objectId={donor?.id}
            objectType="donor"
            objectTypePlural="donors"
            useObjectEntityRecord={useDonorEntityRecord}
            tabDefinitions={tabDefinitions}
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-donors`}
            StatusBadge={() => <StatusBadge status={donor?.status} />}
            SecondaryActionButton={SendEmailButton}
            ContextMenuItems={ContextMenuItems}
        >
            <ConfirmationDialog
                title={__('Delete Donor', 'give')}
                actionLabel={__('Delete Donor', 'give')}
                isOpen={showConfirmationDialog}
                handleClose={() => setShowConfirmationDialog(false)}
                handleConfirm={() => { }}
            >
                {__('Are you sure you want to delete this donor?', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}
