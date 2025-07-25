import { useState } from "react";
import { __ } from "@wordpress/i18n";
import classnames from 'classnames';
import SyncDetails,{ SyncPaymentDetails } from "./Details";
import { SubscriptionSyncResponse } from "../../../hooks/useSubscriptionSync";
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionSyncListProps = {
    syncResult: SubscriptionSyncResponse;
}

/**
 * @unreleased
 */
export default function SubscriptionSyncList({ syncResult }: SubscriptionSyncListProps) {
    const { details, missingTransactions } = syncResult;

    const statusUpdated = details?.currentStatus !== details?.gatewayStatus;
    const periodUpdated = details?.currentPeriod !== details?.gatewayPeriod;
    const createdAtUpdated = details?.currentCreatedAt !== details?.gatewayCreatedAt;
    const paymentsUpdated = missingTransactions?.length > 0;
    const transactions = paymentsUpdated ? missingTransactions : [null];

    return (
        <div className={styles.list}>
            <SyncItem title="Subscription status" isAccurate={!statusUpdated}>
                <SyncDetails isAccurate={!statusUpdated} currentValue={details?.currentStatus} />
            </SyncItem>
            <SyncItem title="Billing period" isAccurate={!periodUpdated}>
                <SyncDetails isAccurate={!periodUpdated} currentValue={details?.currentPeriod} />
            </SyncItem>
            <SyncItem title="Date created" isAccurate={!createdAtUpdated}>
                <SyncDetails isAccurate={!createdAtUpdated} currentValue={details?.currentCreatedAt} />
            </SyncItem>
            <SyncItem title="Subscription payments" isAccurate={!paymentsUpdated}>
                {transactions?.map((transaction) => (<SyncPaymentDetails key={transaction?.id} payment={transaction} isAccurate={!paymentsUpdated}/>))}
            </SyncItem>
        </div>
    );
}

/**
 * @unreleased
 */
type SyncItemProps = {
    title: string;
    isAccurate: boolean | null;
    children: React.ReactNode;
}

/**
 * @unreleased
 */
function SyncItem({ title, isAccurate, children }: SyncItemProps) {
    const [isOpen, setIsOpen] = useState<boolean>(false);

    return (
        <div className={styles.item}>
            <div className={styles.itemContent}>
                <div className={styles.itemHeader}>
                    <div>
                        <span
                            className={classnames(styles.itemPill, {
                                [styles.itemAccurate]: isAccurate,
                            })}
                        >
                            {isAccurate ? __('ACCURATE', 'give') : __('UPDATED', 'give')}
                        </span>
                        <h2 className={styles.itemTitle}>{title}</h2>
                    </div>
                    <button
                        className={styles.itemButton}
                        onClick={() => setIsOpen(!isOpen)}
                    >
                        {isOpen ? __('Close', 'give') : __('View details', 'give')}
                    </button>
                </div>

                {isOpen && children}
            </div>
        </div>
    );
}
