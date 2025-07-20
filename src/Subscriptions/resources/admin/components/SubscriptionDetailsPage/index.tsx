/**
 * External Dependencies
 */
import { useState } from 'react';
import cx from 'classnames';

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal Dependencies
*/
import { TrashIcon } from '@givewp/components/AdminDetailsPage/Icons';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from '@givewp/subscriptions/utils';
import styles from './SubscriptionDetailsPage.module.scss';
import tabDefinitions from './Tabs/definitions';
import { useSubscriptionAmounts } from '@givewp/subscriptions/hooks';
import { useDispatch } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

const { subscriptionStatuses } = getSubscriptionOptionsWindowData();

/**
 * @unreleased
 */
const StatusBadge = ({ status, isTest }: { status: string, isTest: boolean }) => {
    const statusMap = subscriptionStatuses;

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
                    {__('Test Subscription', 'give')}
                </div>
            )}
        </>
    );
};

/**
 * @unreleased
 */
export default function SubscriptionDetailsPage() {
    const { adminUrl, subscriptionsAdminUrl} = getSubscriptionOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);
    const { record: subscription } = useSubscriptionEntityRecord();
    const {formatter} = useSubscriptionAmounts(subscription);
    const { deleteEntityRecord } = useDispatch( coreDataStore );

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                <a
                    href="#"
                    className={cx(className, styles.archive)}
                    onClick={() => setShowConfirmationDialog('delete')}
                >
                    <TrashIcon /> {__('Trash subscription', 'give')}
                </a>
            </>
        );
    };

    /**
     * @unreleased
     */
    const handleDelete = async () => {
        try {
            await deleteEntityRecord('givewp', 'subscription', subscription?.id, {force: false})
            window.location.href = subscriptionsAdminUrl;
        } catch (error) {
            setShowConfirmationDialog(null);
        }
    };


    return (
        <AdminDetailsPage
            objectId={subscription?.id}
            objectType="subscription"
            objectTypePlural="subscriptions"
            useObjectEntityRecord={useSubscriptionEntityRecord}
            tabDefinitions={tabDefinitions}
            breadcrumbUrl={`${adminUrl}edit.php?post_type=give_forms&page=give-subscriptions`}
            breadcrumbTitle={subscription?.id && sprintf('#%s', subscription?.id)}
            pageTitle={subscription?.amount?.value != null ? formatter.format(subscription?.amount?.value) : ''}
            StatusBadge={() => <StatusBadge status={subscription?.status} isTest={subscription?.mode === 'test'} />}
            ContextMenuItems={ContextMenuItems}
        >
            <ConfirmationDialog
                title={__('Move subscription to trash', 'give')}
                actionLabel={__('Trash Subscription', 'give')}
                isOpen={showConfirmationDialog === 'delete'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={handleDelete}
            >
                {__('Are you sure you want to move this subscription to the trash? You can restore it later if needed.', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}
