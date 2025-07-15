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
import { useDonationDelete } from '@givewp/donations/hooks/useDonationDelete';
import { useDonationAmounts } from '@givewp/donations/hooks';

const { donationStatuses } = getDonationOptionsWindowData();

/**
 * @unreleased
 */
const StatusBadge = ({ status }: { status: string }) => {
    const statusMap = donationStatuses;

    if (!statusMap[status]) {
        return null;
    }

    return (
        <div className={`${styles.statusBadge} ${styles[`statusBadge--${status}`]}`}>
            {statusMap[status]}
        </div>
    );
};

/**
 * @unreleased
 */
export default function DonationDetailsPage() {
    const { adminUrl} = getDonationOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);
    const { record: donation } = useDonationEntityRecord();
    const {formatter} = useDonationAmounts(donation);
    const {canRefund, refund, isRefunding, isRefunded} = useDonationRefund(donation);
    const {deleteDonation} = useDonationDelete();

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
                    <TrashIcon /> {__('Delete Donation', 'give')}
                </a>
            </>
        );
    };

    /**
     * @unreleased
     */
    const handleRefund = async () => {
        try {
            await refund();
        } catch (error) {
            setShowConfirmationDialog(null);
        }
    };

    /**
     * @unreleased
     */
    const handleDelete = async () => {
        try {
            await deleteDonation(donation?.id);
            setShowConfirmationDialog(null);
            window.location.href = `${adminUrl}edit.php?post_type=give_forms&page=give-payment-history`;
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
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-donations`}
            breadcrumbTitle={sprintf('#%s', donation?.id)}
            pageTitle={formatter.format(donation?.amount?.value)}
            StatusBadge={() => <StatusBadge status={donation?.status} />}
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
