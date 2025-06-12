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
import { RefundIcon, TrashIcon, ViewIcon } from '@givewp/components/AdminDetailsPage/Icons';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import { getDonationOptionsWindowData, useDonationEntityRecord } from '@givewp/donations/utils';
import styles from './DonationDetailsPage.module.scss';
import tabDefinitions from './Tabs/definitions';
import { amountFormatter } from '@givewp/components/AdminDetailsPage/utils';

/**
 * @unreleased
 */
const StatusBadge = ({ status }: { status: string }) => {
    const statusMap = {
        pending: __('Pending', 'give'),
        processing: __('Processing', 'give'),
        publish: __('Completed', 'give'),
        refunded: __('Refunded', 'give'),
        failed: __('Failed', 'give'),
        cancelled: __('Cancelled', 'give'),
        abandoned: __('Abandoned', 'give'),
        preapproval: __('Preapproval', 'give'),
        revoked: __('Revoked', 'give'),
    };

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
    const { adminUrl, currency: defaultCurrency } = getDonationOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);

    const { record: donation } = useDonationEntityRecord();
    const currencyFormatter = amountFormatter(donation?.amount?.currency ?? defaultCurrency);

    console.log(donation);

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                {donation.status === 'publish' && (
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

    return (
        <AdminDetailsPage
            objectId={donation?.id}
            objectType="donation"
            objectTypePlural="donations"
            useObjectEntityRecord={useDonationEntityRecord}
            tabDefinitions={tabDefinitions}
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-donations`}
            breadcrumbTitle={sprintf('#%s', donation?.id)}
            pageTitle={currencyFormatter.format(donation?.amount?.value)}
            StatusBadge={() => <StatusBadge status={donation?.status} />}
            ContextMenuItems={ContextMenuItems}
        >
            <ConfirmationDialog
                title={__('Refund Donation', 'give')}
                actionLabel={__('Refund Donation', 'give')}
                isOpen={showConfirmationDialog === 'refund'}
                variant="regular"
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={() => { }}
            >
                {
                    createInterpolateElement(
                        sprintf(
                            __('Refund <strong>%s</strong> to <strong>%s</strong>', 'give'),
                            currencyFormatter.format(donation?.amount?.value),
                            donation?.firstName
                        ),
                        {
                            strong: <strong />
                        }
                    )
                }
            </ConfirmationDialog>
            <ConfirmationDialog
                title={__('Delete Donation', 'give')}
                actionLabel={__('Delete Donation', 'give')}
                isOpen={showConfirmationDialog === 'delete'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={() => { }}
            >
                {__('Are you sure you want to delete this donation?', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}
