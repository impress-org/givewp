import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import { useDonationsBySubscription, useSubscriptionAmounts } from "@givewp/subscriptions/hooks";
import SubscriptionSummary from "./SubscriptionSummary";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {mode, adminUrl} = getSubscriptionOptionsWindowData();
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();
    const {record: donation, hasResolved: donationResolved, isResolving: donationLoading} = useDonationsBySubscription(subscription?.id, mode);
    const {intendedAmount} = useSubscriptionAmounts(subscription);

    return (
        <div className={styles.overview}>

            <div className={styles.left}>

            </div>

            <div className={styles.right}>
                <SubscriptionSummary subscription={subscription} donation={donation} adminUrl={adminUrl} intendedAmount={intendedAmount} isLoading={isResolving || !hasResolved || donationLoading || !donationResolved} />
            </div>
        </div>
    )
}
