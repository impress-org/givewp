/**
 * External Dependencies
 */
import { useState } from 'react';
import cx from 'classnames';
import { useFormContext } from 'react-hook-form';

/**
 * WordPress Dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal Dependencies
*/
import { CancelIcon, TrashIcon } from '@givewp/components/AdminDetailsPage/Icons';
import AdminDetailsPage from '@givewp/components/AdminDetailsPage';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from '@givewp/subscriptions/utils';
import tabDefinitions from './Tabs/definitions';
import { useSubscriptionAmounts } from '@givewp/subscriptions/hooks';
import { useDispatch } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import useSubscriptionSync from '@givewp/subscriptions/hooks/useSubscriptionSync';
import SubscriptionSyncList from '../SubscriptionSyncList';
import styles from './SubscriptionDetailsPage.module.scss';
import CancelSubscriptionDialog from './components/CancelSubscriptionDialog';

const { subscriptionStatuses } = getSubscriptionOptionsWindowData();

/**
 * @since 4.8.0
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
 * @since 4.8.0
 */
export default function SubscriptionDetailsPage() {
    const { adminUrl, subscriptionsAdminUrl } = getSubscriptionOptionsWindowData();
    const [showConfirmationDialog, setShowConfirmationDialog] = useState<string | null>(null);
    const [hasSyncBeenPerformed, setHasSyncBeenPerformed] = useState(false);

    // Get subscription ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const subscriptionId = urlParams.get('id');

    const { record: subscription } = useSubscriptionEntityRecord(subscriptionId ? parseInt(subscriptionId) : undefined);
    const { formatter } = useSubscriptionAmounts(subscription);
    const { deleteEntityRecord } = useDispatch(coreDataStore);
    const { syncSubscription, isLoading, hasResolved, syncResult } = useSubscriptionSync();
    const subscriptionCanSync = subscription?.gateway?.canSync;

    const PageTitle = () => {
        if (subscription?.amount?.value == null) {
            return null;
        }
        const periodsLabels = {
            day: _n('day', 'days', subscription?.frequency, 'give'),
            week: _n('week', 'weeks', subscription?.frequency, 'give'),
            month: _n('month', 'months', subscription?.frequency, 'give'),
            quarter: _n('quarter', 'quarters', subscription?.frequency, 'give'),
            year: _n('year', 'years', subscription?.frequency, 'give'),
        };

        const period = [
            __('every', 'give'),
            subscription?.frequency > 1 ? subscription?.frequency : '',
            periodsLabels[subscription?.period],
        ].filter(Boolean).join(' ');

        return (
            <>
                {formatter.format(subscription?.amount?.value)} <span className={styles.period}>{period}</span>
            </>
        );
    };

    function SecondaryActionButton({ className }: { className: string }) {
        return (
            <button
                type="button"
                className={className}
                onClick={() => {
                    setShowConfirmationDialog('sync');
                    setHasSyncBeenPerformed(false);
                }}
            >
                {__('Sync subscription', 'give')}
            </button>
        );
    }

    const ContextMenuItems = ({ className }: { className: string }) => {
        return (
            <>
                {subscription?.status !== 'cancelled' ? (
                    <a
                        href="#"
                        className={cx(className, styles.archive)}
                        onClick={() => setShowConfirmationDialog('cancel')}
                    >
                        <CancelIcon /> {__('Cancel subscription', 'give')}
                    </a>
                ) : (
                    <a
                        href="#"
                        className={cx(className, styles.archive)}
                        onClick={() => setShowConfirmationDialog('delete')}
                    >
                        <TrashIcon /> {__('Trash subscription', 'give')}
                    </a>
                )}
            </>
        );
    };

    /**
     * @since 4.8.0
     */
    const handleDelete = async () => {
        try {
            await deleteEntityRecord('givewp', 'subscription', subscription?.id, { force: false })
            window.location.href = subscriptionsAdminUrl;
        } catch (error) {
            setShowConfirmationDialog(null);
        }
    };

    /**
     * @since 4.8.0
     */
    const handleSyncSubscription = async () => {
        try {
            await syncSubscription(subscription);
            setHasSyncBeenPerformed(true);
            console.log('Sync result:', syncResult);
        } catch (error) {
            console.error('Sync failed:', error);
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
            pageTitle={<PageTitle />}
            SecondaryActionButton={subscriptionCanSync && SecondaryActionButton}
            StatusBadge={() => <StatusBadge status={subscription?.status} isTest={subscription?.mode === 'test'} />}
            ContextMenuItems={ContextMenuItems}
        >
            <CancelSubscriptionDialog
                subscription={subscription}
                showConfirmationDialog={showConfirmationDialog}
                setShowConfirmationDialog={setShowConfirmationDialog}
            />
            <ConfirmationDialog
                title={__('Move subscription to trash', 'give')}
                actionLabel={__('Trash Subscription', 'give')}
                isOpen={showConfirmationDialog === 'delete'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={handleDelete}
            >
                {__('Are you sure you want to move this subscription to the trash? You can restore it later if needed.', 'give')}
            </ConfirmationDialog>
            <ConfirmationDialog
                variant={isLoading ? 'syncing' : null}
                spinner={'arc'}
                isConfirming={isLoading}
                title={__('Sync subscription details', 'give')}
                actionLabel={isLoading ? __('Syncing', 'give') : !hasSyncBeenPerformed ? __('Proceed to sync', 'give') : __('Resync', 'give')}
                showCancelButton={false}
                isOpen={showConfirmationDialog === 'sync'}
                handleClose={() => {
                    setShowConfirmationDialog(null);
                }}
                handleConfirm={handleSyncSubscription}
                footer={
                    hasSyncBeenPerformed && hasResolved && syncResult?.notice && (
                    <div className={styles.syncModalFooter}>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fillRule="evenodd" clipRule="evenodd" d="M10 .832a9.167 9.167 0 1 0 0 18.333A9.167 9.167 0 0 0 10 .832zm0 5a.833.833 0 1 0 0 1.667h.008a.833.833 0 0 0 0-1.667H10zm.833 4.167a.833.833 0 0 0-1.666 0v3.333a.833.833 0 1 0 1.666 0V9.999z" fill="#0C7FF2"/>
                        </svg>
                        {syncResult?.notice}
                    </div>
                    )
                }
            >
                {hasSyncBeenPerformed && hasResolved ? <SubscriptionSyncList syncResult={syncResult} /> : __('This will update the subscription details using the most recent data from the gateway. However, no changes will be made to existing payments.', 'give')}
            </ConfirmationDialog>
        </AdminDetailsPage>
    );
}
