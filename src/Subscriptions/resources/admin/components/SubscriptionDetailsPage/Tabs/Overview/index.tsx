
import { __ } from "@wordpress/i18n";
import SubscriptionStats from "./SubscriptionStats";
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import Header from "@givewp/admin/components/Header";
import { SubscriptionNotes } from "@givewp/admin/components/PrivateNotes";
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import { useDonationsBySubscription } from "@givewp/subscriptions/hooks";
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
                <OverviewPanel>
                    <SubscriptionNotes subscriptionId={subscription?.id} />
                </OverviewPanel>

            </div>

            <div className={styles.right}>

            </div>
        </div>
    );
}
