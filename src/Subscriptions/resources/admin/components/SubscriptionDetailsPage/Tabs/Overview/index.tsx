
import { __ } from "@wordpress/i18n";
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import Header from "@givewp/admin/components/Header";
import { SubscriptionNotes } from "@givewp/admin/components/PrivateNotes";
import { useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const {record: subscription, hasResolved } = useSubscriptionEntityRecord();

    return (
        <div className={styles.overview}>

            <div className={styles.left}>
                <OverviewPanel>
                    <SubscriptionNotes subscriptionId={subscription?.id} />
                </OverviewPanel>

            </div>

            <div className={styles.right}>

            </div>
        </div>
    );
}
