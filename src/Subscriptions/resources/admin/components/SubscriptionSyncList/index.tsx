import { useState } from "react";
import { __ } from "@wordpress/i18n";
import classnames from 'classnames';
import SyncDetails,{ PaymentDetails } from "./Details";
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
    const { details, missingTransactions, presentTransactions } = syncResult;

    const statusUpdated = details?.currentStatus !== details?.gatewayStatus;
    const periodUpdated = details?.currentPeriod !== details?.gatewayPeriod;
    const createdAtUpdated = details?.currentCreatedAt !== details?.gatewayCreatedAt;

    return (
        <div className={styles.list}>
            <SyncItem title="Subscription status" isUpdated={statusUpdated}>
                <SyncDetails isUpdated={statusUpdated} currentValue={details?.currentStatus} />
            </SyncItem>
            <SyncItem title="Billing period" isUpdated={periodUpdated}>
                <SyncDetails isUpdated={periodUpdated} currentValue={details?.currentPeriod} />
            </SyncItem>
            <SyncItem title="Date created" isUpdated={createdAtUpdated}>
                <SyncDetails isUpdated={createdAtUpdated} currentValue={details?.currentCreatedAt} />
            </SyncItem>
            <SyncItem title="Subscription payments" isUpdated={false}>
                <PaymentDetails payment={missingTransactions || []} />
            </SyncItem>
        </div>
    );
}

/**
 * @unreleased
 */
type SyncItemProps = {
    title: string;
    isUpdated: boolean | null;
    children: React.ReactNode;
}

/**
 * @unreleased
 */
function SyncItem({ title, isUpdated, children }: SyncItemProps) {
    const [isOpen, setIsOpen] = useState<boolean>(false);

    // Add debugging for the SyncItem
    console.log(`SyncItem "${title}" isUpdated:`, isUpdated);

    return (
        <div className={styles.item}>
            <div className={styles.itemContent}>
                <div className={styles.itemHeader}>
                    <div>
                        <span
                            className={classnames(styles.itemPill, {
                                [styles.itemUpdated]: isUpdated,
                            })}
                        >
                            {isUpdated ? __('UPDATED', 'give') : __('ACCURATE', 'give')}
                        </span>
                        <h2 className={styles.itemTitle}>{title}</h2>
                    </div>
                    <button
                        className={styles.itemButton}
                        onClick={() => setIsOpen(!isOpen)}
                    >
                        {isOpen ? 'Close' : 'View details'}
                    </button>
                </div>

                {isOpen && children}
            </div>
        </div>
    );
}
