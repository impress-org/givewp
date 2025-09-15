import { useState } from "react";
import { getSubscriptionOptionsWindowData } from "../utils";

export interface SubscriptionSyncResponse {
	details: SyncDetails;
	missingTransactions: SyncTransaction[];
	presentTransactions: SyncTransaction[];
	notice: string;
  }

  export interface SyncDetails {
	currentStatus: string;
	gatewayStatus: string;
	currentPeriod: string;
	gatewayPeriod: string;
	currentCreatedAt: string;
	gatewayCreatedAt: string;
  }

  export interface SyncTransaction {
	id: number;
	gatewayTransactionId: string;
	amount: string;
	createdAt: string;
	status: string;
	type: 'renewal' | 'subscription';
  }

/**
 * @since 4.8.0
 */
export default function useSubscriptionSync() {
	const { syncSubscriptionNonce } = getSubscriptionOptionsWindowData();

	const [isLoading, setIsLoading] = useState(false);
	const [hasResolved, setHasResolved] = useState(false);
	const [syncResult, setSyncResult] = useState<SubscriptionSyncResponse | null>(null);

	const syncSubscription = async (subscription: any) => {
		setIsLoading(true);
		setHasResolved(false);

		try {
			const response = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'give_recurring_sync_subscription_details',
					subscription_id: String(subscription?.id),
					'give-form-id': String(subscription?.donationFormId),
					security: syncSubscriptionNonce,
				}),
			});

			const json = await response.json();

			setSyncResult(json?.data);
			setHasResolved(true);
			return json?.data;
		} catch (error) {
			console.error('Sync failed:', error);
			setHasResolved(false);
			throw error;
		} finally {
			setIsLoading(false);
		}
	};

	return {
		syncSubscription,
		isLoading,
		hasResolved,
		syncResult: syncResult,
	};
}
