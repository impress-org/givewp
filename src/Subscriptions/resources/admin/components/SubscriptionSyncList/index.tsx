import { useState } from "react";
import { __ } from "@wordpress/i18n";
import classnames from 'classnames';
import SyncDetails,{ PaymentDetails } from "./Details";
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export default function SubscriptionSyncList() {
    return (
        <div className={styles.list}>
            <SyncItem title="Subscription status" isUpdated={true}>
                <SyncDetails isUpdated={true} />
            </SyncItem>
            <SyncItem title="Billing period" isUpdated={false}>
                <SyncDetails isUpdated={false} />
            </SyncItem>
            <SyncItem title="Date created" isUpdated={true}>
                <SyncDetails isUpdated={true} />
            </SyncItem>
            <SyncItem title="Subscription payments" isUpdated={false}>
                <PaymentDetails payment={[]} />
            </SyncItem>
        </div>
    );
}

/**
 * @unreleased
 */
type SyncItemProps = {
    title: string;
    isUpdated: boolean;
    children: React.ReactNode;
}

/**
 * @unreleased
 */
function SyncItem({ title, isUpdated, children }: SyncItemProps) {
    const [isOpen, setIsOpen] = useState<boolean>(false);

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
