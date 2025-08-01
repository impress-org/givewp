/**
 * External Dependencies
 */
import { useState } from 'react';
import cx from 'classnames';

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Internal Dependencies
*/
import { RefundIcon, TrashIcon } from '@givewp/components/AdminDetailsPage/Icons';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import { getDonationOptionsWindowData, useDonationEntityRecord } from '@givewp/donations/utils';
import styles from './DonationDetailsPage.module.scss';
import tabDefinitions from './Tabs/definitions';
import useDonationRefund from '@givewp/donations/hooks/useDonationRefund';
import { useDonationAmounts } from '@givewp/donations/hooks';
import { useDispatch } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

const { donationStatuses } = getDonationOptionsWindowData();

/**
 * @since 4.6.0
 */
const StatusBadge = ({ status, isTest }: { status: string, isTest: boolean }) => {
    const statusMap = donationStatuses;

    if (!statusMap[status]) {
        return null;
    }

    return (
        <>
            <div className={`${styles.statusBadge} ${styles[`statusBadge--${status}`]}`}>
                {statusMap[status]}
            </div>
            {isTest && (
                <div className={`${styles.statusBadge} ${styles.testBadge}`}>
                    {__('Test Donation', 'give')}
                </div>
            )}
        </>
    );
};

/**
 * @since 4.6.0
 */
export default function DonationDetailsPage() {
    const { adminUrl, donationsAdminUrl} = getDonationOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);
    const { record: donation } = useDonationEntityRecord();
    const {formatter} = useDonationAmounts(donation);
    const {canRefund, refund, isRefunding, isRefunded} = useDonationRefund(donation);
    const { deleteEntityRecord } = useDispatch( coreDataStore );

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                {canRefund && (
                    <a
                        href="#"
                        className={className}
                        onClick={() => setShowConfirmationDialog('refund')}
                    >
                        <RefundIcon /> {__('Refund', 'give')}
                    </a>
                )}
                <a
                    href="#"
                    className={cx(className, styles.archive)}
                    onClick={() => setShowConfirmationDialog('delete')}
                >
                    <TrashIcon /> {__('Trash donation', 'give')}
                </a>
            </>
        );
    };

    /**
     * @since 4.6.0
     */
    const handleRefund = async () => {
        try {
            await refund();
        } catch (error) {
            setShowConfirmationDialog(null);
        }
    };

    /**
     * @since 4.6.0
     */
    const handleDelete = async () => {
        try {
            await deleteEntityRecord('givewp', 'donation', donation?.id, {force: false})
            window.location.href = donationsAdminUrl;
        } catch (error) {
            setShowConfirmationDialog(null);
        }
    };


    return (
        <AdminDetailsPage
            objectId={donation?.id}
            objectType="donation"
            objectTypePlural="donations"
            useObjectEntityRecord={useDonationEntityRecord}
            tabDefinitions={tabDefinitions}
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-payment-history`}
            breadcrumbTitle={donation?.id && sprintf('#%s', donation?.id)}
            pageTitle={donation?.amount?.value != null ? formatter.format(donation?.amount?.value) : ''}
            StatusBadge={() => <StatusBadge status={donation?.status} isTest={donation?.mode === 'test'} />}
            ContextMenuItems={ContextMenuItems}
        >
            <ConfirmationDialog
                title={__('Refund Donation', 'give')}
                actionLabel={__('Refund Donation', 'give')}
                isOpen={showConfirmationDialog === 'refund' && !isRefunded}
                variant="regular"
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={handleRefund}
                isConfirming={isRefunding}
            >
                {
                    createInterpolateElement(
                        sprintf(
                            __('Refund <strong>%s</strong> to <strong>%s</strong>', 'give'),
                            formatter.format(donation?.amount?.value),
                            donation?.firstName
                        ),
                        {
                            strong: <strong />
                        }
                    )
                }
            </ConfirmationDialog>
            <ConfirmationDialog
                title={__('Move donation to trash', 'give')}
                actionLabel={__('Trash Donation', 'give')}
                isOpen={showConfirmationDialog === 'delete'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={handleDelete}
            >
                {__('Are you sure you want to move this donation to the trash? You can restore it later if needed.', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}
