import { useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import { useSubscriptionAmounts } from "@givewp/subscriptions/hooks";
import styles from "./styles.module.scss";
import SubscriptionSummary from "./SubscriptionSummary";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();
    const {intendedAmount} = useSubscriptionAmounts(subscription);

    return (
        <div className={styles.overview}>

            <div className={styles.left}>

            </div>

            <div className={styles.right}>
                <SubscriptionSummary subscription={subscription} intendedAmount={intendedAmount} />
            </div>
        </div>
    )
}
