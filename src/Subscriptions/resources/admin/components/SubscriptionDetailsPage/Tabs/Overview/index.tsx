import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import SubscriptionPaymentDetails from "./SubscriptionPaymentDetails";
import styles from "./styles.module.scss";
import { useDonationsBySubscription } from "@givewp/subscriptions/hooks/useDonationsBySubscription";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {mode, adminUrl} = getSubscriptionOptionsWindowData();
    const {record: subscription, hasResolved, isResolving } = useSubscriptionEntityRecord();
    const {record: donation, hasResolved: donationsResolved, isResolving: donationsLoading} = useDonationsBySubscription(subscription?.id, mode);

    return (
        <div className={styles.overview}>

            <div className={styles.left}>
                <SubscriptionPaymentDetails subscription={subscription} donation={donation} />
            </div>

            <div className={styles.right}>
            </div>
        </div>
    );
}
