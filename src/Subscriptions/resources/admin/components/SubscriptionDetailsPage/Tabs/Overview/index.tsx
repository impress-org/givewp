import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import { useDonationsBySubscription } from "@givewp/subscriptions/hooks";

import SubscriptionStats from "./SubscriptionStats";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {mode, adminUrl} = getSubscriptionOptionsWindowData();
    const {record: subscription, hasResolved } = useSubscriptionEntityRecord();
    const {records: donations, hasResolved: donationsResolved, isResolving: donationsLoading} = useDonationsBySubscription(subscription?.id, mode);
    
    return (
        <div className={styles.overview}>
            <SubscriptionStats donations={donations} currency={subscription?.amount?.currency} totalInstallments={subscription?.installments} loading={!hasResolved || donationsLoading || !donationsResolved} />
            <div className={styles.left}>

            </div>

            <div className={styles.right}>

            </div>
        </div>
    );
}
