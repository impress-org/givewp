import { useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import SubscriptionPaymentDetails from "./SubscriptionPaymentDetails";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();

    return (
        <div className={styles.overview}>

            <div className={styles.left}>
                <SubscriptionPaymentDetails subscription={subscription} />
            </div>

            <div className={styles.right}>
            </div>
        </div>
    );
}
