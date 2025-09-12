
import SubscriptionStats from "./SubscriptionStats";
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import SubscriptionSummaryGrid from "./SubscriptionSummaryGrid";
import { SubscriptionNotes } from "@givewp/admin/components/PrivateNotes";
import { useDonationsBySubscription, useSubscriptionAmounts } from '@givewp/subscriptions/hooks';
import SubscriptionSummary from '@givewp/subscriptions/admin/components/SubscriptionDetailsPage/Tabs/Overview/SubscriptionSummary';
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import SubscriptionAnnualProjection from "./SubscriptionAnnualProjection";
import styles from "./styles.module.scss";

/**
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const { mode, adminUrl } = getSubscriptionOptionsWindowData();
    const { record: subscription, hasResolved: subscriptionsResolved, isResolving: subscriptionLoading } = useSubscriptionEntityRecord();
    const { intendedAmount } = useSubscriptionAmounts(subscription);
    const { records: donations, hasResolved: donationsResolved, isResolving: donationsLoading } = useDonationsBySubscription(subscription?.id, mode);

    return (
        <div className={styles.overview}>
            <SubscriptionStats donations={donations} currency={subscription?.amount?.currency} totalInstallments={subscription?.installments} loading={!subscriptionsResolved || donationsLoading || !donationsResolved} />

            <div className={styles.left}>
                <SubscriptionSummaryGrid subscription={subscription} donation={donations?.[0]} isLoading={subscriptionLoading || donationsLoading} />
                <OverviewPanel>
                    <SubscriptionNotes subscriptionId={subscription?.id} />
                </OverviewPanel>

            </div>

            <div className={styles.right}>
                <SubscriptionAnnualProjection donations={donations} currency={subscription?.amount?.currency} subscription={subscription} />
                <SubscriptionSummary subscription={subscription} donation={donations?.[0]} adminUrl={adminUrl} intendedAmount={intendedAmount} isLoading={subscriptionLoading || !subscriptionsResolved || donationsLoading || !donationsResolved} />
            </div>
        </div>
    );
}
