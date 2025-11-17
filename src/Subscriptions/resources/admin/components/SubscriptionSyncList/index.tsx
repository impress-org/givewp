import { useState } from "react";
import { __ } from "@wordpress/i18n";
import classnames from 'classnames';
import SyncDetails,{ SyncPaymentDetails } from "./Details";
import { SubscriptionSyncResponse } from "../../../hooks/useSubscriptionSync";
import styles from './styles.module.scss';

/**
 * @since 4.8.0
 */
type SubscriptionSyncListProps = {
    syncResult: SubscriptionSyncResponse;
}

/**
 * @since 4.8.0
 */
export default function SubscriptionSyncList({ syncResult }: SubscriptionSyncListProps) {
    const { details, missingTransactions } = syncResult;
    const [openItem, setOpenItem] = useState<string | null>(null);

    const statusUpdated = details?.currentStatus !== details?.gatewayStatus;
    const periodUpdated = details?.currentPeriod !== details?.gatewayPeriod;
    const createdAtUpdated = details?.currentCreatedAt !== details?.gatewayCreatedAt;
    const paymentsUpdated = missingTransactions?.length > 0;
    const transactions = paymentsUpdated ? missingTransactions : [null];

    const handleItemToggle = (itemId: string) => {
        setOpenItem(openItem === itemId ? null : itemId);
    };

    return (
        <div className={styles.list}>
            <SyncItem
                id="status"
                title="Subscription status"
                isAccurate={!statusUpdated}
                isOpen={openItem === "status"}
                onToggle={() => handleItemToggle("status")}
            >
                <SyncDetails isAccurate={!statusUpdated} platform={details?.currentStatus} gateway={details?.gatewayStatus} />
            </SyncItem>
            <SyncItem
                id="period"
                title="Billing period"
                isAccurate={!periodUpdated}
                isOpen={openItem === "period"}
                onToggle={() => handleItemToggle("period")}
            >
                <SyncDetails isAccurate={!periodUpdated} platform={details?.currentPeriod} gateway={details?.gatewayPeriod} />
            </SyncItem>
            <SyncItem
                id="created"
                title="Date created"
                isAccurate={!createdAtUpdated}
                isOpen={openItem === "created"}
                onToggle={() => handleItemToggle("created")}
            >
                <SyncDetails isAccurate={!createdAtUpdated} platform={details?.currentCreatedAt} gateway={details?.gatewayCreatedAt} />
            </SyncItem>
            <SyncItem
                id="payments"
                title="Subscription payments"
                isAccurate={!paymentsUpdated}
                isOpen={openItem === "payments"}
                onToggle={() => handleItemToggle("payments")}
            >
                {transactions?.map((transaction, index) => (<SyncPaymentDetails key={transaction?.id ?? `sync-payment-${index}`} isAccurate={!paymentsUpdated} payment={transaction} />))}
            </SyncItem>
        </div>
    );
}

/**
 * @since 4.8.0
 */
type SyncItemProps = {
    id: string;
    title: string;
    isAccurate: boolean | null;
    isOpen: boolean;
    onToggle: () => void;
    children: React.ReactNode;
}

/**
 * @since 4.8.0
 */
function SyncItem({ id, title, isAccurate, isOpen, onToggle, children }: SyncItemProps) {
    return (
        <div id={id} className={styles.item}>
            <div className={styles.itemContent}>
                <div className={styles.itemHeader}>
                    <div>
                        <span
                            className={classnames(styles.itemPill, {
                                [styles.itemAccurate]: isAccurate,
                            })}
                            role="status"
                            aria-label={isAccurate ? __('ACCURATE', 'give') : __('UPDATED', 'give')}
                        >
                            {isAccurate ? __('ACCURATE', 'give') : __('UPDATED', 'give')}
                        </span>
                        <h2 className={styles.itemTitle}>{title}</h2>
                    </div>
                    <button
                        className={styles.itemButton}
                        onClick={onToggle}
                    >
                        {isOpen ? __('Close', 'give') : __('View details', 'give')}
                    </button>
                </div>

                {isOpen && children}
            </div>
        </div>
    );
}
