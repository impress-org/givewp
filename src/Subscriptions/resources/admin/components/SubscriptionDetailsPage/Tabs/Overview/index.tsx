
import { __ } from "@wordpress/i18n";
import SubscriptionStats from "./SubscriptionStats";
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import { SubscriptionNotes } from "@givewp/admin/components/PrivateNotes";
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import {useDonationsBySubscription, useDonationBySubscription, useSubscriptionAmounts} from '@givewp/subscriptions/hooks';
import styles from "./styles.module.scss";
import SubscriptionSummary
    from '@givewp/subscriptions/admin/components/SubscriptionDetailsPage/Tabs/Overview/SubscriptionSummary';

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {mode, adminUrl} = getSubscriptionOptionsWindowData();
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();
    const {intendedAmount} = useSubscriptionAmounts(subscription);
    const {records: donations, hasResolved: donationsResolved, isResolving: donationsLoading} = useDonationsBySubscription(subscription?.id, mode);
    const {record: donation, hasResolved: donationResolved, isResolving: donationLoading} = useDonationBySubscription(subscription?.id, mode);

    return (
        <div className={styles.overview}>
            <SubscriptionStats donations={donations} currency={subscription?.amount?.currency} totalInstallments={subscription?.installments} loading={!hasResolved || donationsLoading || !donationsResolved} />

            <div className={styles.left}>
                <OverviewPanel>
                    <SubscriptionNotes subscriptionId={subscription?.id} />
                </OverviewPanel>

            </div>

            <div className={styles.right}>
                <SubscriptionSummary subscription={subscription} donation={donation} adminUrl={adminUrl} intendedAmount={intendedAmount} isLoading={isResolving || !hasResolved || donationLoading || !donationResolved} />
            </div>
        </div>
    );
}
