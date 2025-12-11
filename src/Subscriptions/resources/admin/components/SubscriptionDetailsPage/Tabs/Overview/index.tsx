
import SubscriptionStats from "./SubscriptionStats";
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import SubscriptionSummaryGrid from "./SubscriptionSummaryGrid";
import { SubscriptionNotes } from "@givewp/admin/components/PrivateNotes";
import { useSubscriptionAmounts } from '@givewp/subscriptions/hooks';
import { getSubscriptionEmbeds } from '@givewp/subscriptions/common';
import SubscriptionSummary from '@givewp/subscriptions/admin/components/SubscriptionDetailsPage/Tabs/Overview/SubscriptionSummary';
import { getSubscriptionOptionsWindowData, useSubscriptionEntityRecord } from "@givewp/subscriptions/utils";
import SubscriptionAnnualProjection from "./SubscriptionAnnualProjection";
import styles from "./styles.module.scss";
import { Spinner } from "@givewp/admin/components";
import { __ } from '@wordpress/i18n';

/**
 * @since 4.10.0 updated logic to use embed relationships
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageOverviewTab() {
    const { adminUrl } = getSubscriptionOptionsWindowData();
    const { record: subscription, hasResolved: subscriptionsResolved, isResolving: subscriptionLoading } = useSubscriptionEntityRecord();
    const { intendedAmount } = useSubscriptionAmounts(subscription);
    const { donations } = getSubscriptionEmbeds(subscription);

    if (subscriptionLoading || !subscriptionsResolved) {
        return <Spinner />;
    }

    if (!subscription) {
        return <p>{__('No subscription found', 'give')}</p>;
    }

    return (
        <div className={styles.overview}>
            <SubscriptionStats donations={donations} currency={subscription?.amount?.currency} totalInstallments={subscription?.installments} loading={subscriptionLoading || !subscriptionsResolved} />

            <div className={styles.left}>
                <SubscriptionSummaryGrid subscription={subscription} isLoading={subscriptionLoading || !subscriptionsResolved} />
                <OverviewPanel>
                    <SubscriptionNotes subscriptionId={subscription?.id} />
                </OverviewPanel>

            </div>

            <div className={styles.right}>
                <SubscriptionAnnualProjection donations={donations} currency={subscription?.amount?.currency} subscription={subscription} />
                <SubscriptionSummary subscription={subscription} adminUrl={adminUrl} intendedAmount={intendedAmount} isLoading={subscriptionLoading || !subscriptionsResolved} />
            </div>
        </div>
    );
}
