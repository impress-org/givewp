import { useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import SubscriptionStats from "./SubscriptionStats";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();

    return (
        <div className={styles.overview}>
            <SubscriptionStats totalContributions={350} paymentsCompleted={3} totalInstallments={subscription.installments} loading={isResolving || !hasResolved} />

            <div className={styles.left}>

            </div>

            <div className={styles.right}>

            </div>
        </div>
    );
}
