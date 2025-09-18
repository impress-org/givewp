import ConfirmationDialog from "@givewp/components/AdminDetailsPage/ConfirmationDialog";
import { __ } from "@wordpress/i18n";
import styles from "../SubscriptionDetailsPage.module.scss";
import { useState } from "react";
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import useSubscriptionCancel from "@givewp/subscriptions/hooks/useSubscriptionCancel";
import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";
import { TriangleIcon } from "@givewp/components/AdminDetailsPage/Icons";

/**
 * @since 4.8.0
 */
export default function CancelSubscriptionDialog({
    subscription,
    showConfirmationDialog,
    setShowConfirmationDialog,
}: {
    subscription: Subscription;
    showConfirmationDialog: string;
    setShowConfirmationDialog: (showConfirmationDialog: string) => void;
}) {
    const [trashSubscription, setTrashSubscription] = useState(false);
    const {cancel} = useSubscriptionCancel(subscription);
    const { subscriptionsAdminUrl } = getSubscriptionOptionsWindowData();
    const hasPaymentMethodDetails = subscription?.gateway?.id;

    const handleCancel = async () => {
        try {
            await cancel(trashSubscription);

            if (trashSubscription) {
                window.location.href = subscriptionsAdminUrl;
            }
        } finally {
            setShowConfirmationDialog(null);
        }
    };

    return (
        <ConfirmationDialog
                title={__('Cancel subscription', 'give')}
                actionLabel={hasPaymentMethodDetails ? __('Proceed', 'give') : __('Proceed anyway', 'give')}
                isOpen={showConfirmationDialog === 'cancel'}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={handleCancel}
            >
                {hasPaymentMethodDetails
                    ? __("If you cancel this subscription, you won't receive any more payments from this subscription. Do you want to proceed?", 'give')
                    : <>
                        <GatewayNotice />
                        {__("This will not cancel the subscription at the gateway. Do you want to proceed anyway?", 'give')}
                    </>
                }
                <div className={styles.checkboxField}>
                    <label className={styles.checkboxLabel}>
                        <input
                            id="cancel-subscription-checkbox"
                            type="checkbox"
                            className={styles.checkbox}
                            checked={trashSubscription}
                            onChange={(e) => setTrashSubscription(e.target.checked)}
                            aria-describedby="trash-subscription-description"
                        />
                        <span id="trash-subscription-description">
                            {__('Move subscription to trash after cancelling', 'give')}
                        </span>
                    </label>
                </div>
            </ConfirmationDialog>
    );
}

/**
 * @since 4.8.0
 */
function GatewayNotice() {
    return (
        <div className={styles.cancelDialogNotice}>
            <TriangleIcon />
            <p>{__('Subscription gateway isn’t active on your site.', 'give')}</p>
        </div>
    );
}
