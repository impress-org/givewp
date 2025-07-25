import { __ } from '@wordpress/i18n';
import type {Donation} from '@givewp/donations/admin/components/types';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import PaymentDetails from '@givewp/admin/components/PaymentDetails';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export type DonationSummaryGridProps = {
    donation: Donation;
};

/**
 * @unreleased
 */
export default function DonationPaymentDetails({donation,}: DonationSummaryGridProps) {
     const subscriptionPageUrl = donation?.subscriptionId ? `edit.php?post_type=give_forms&page=give-subscriptions&view=overview&id=${donation?.subscriptionId}` : null;
     const isRecurringDonation = !!donation?.subscriptionId;

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>
            <PaymentDetails
                record={donation}
                gatewayLinkLabel={__('View donation on gateway', 'give')}
                subscriptionPageUrl={subscriptionPageUrl}
                infoCardTitle={__('Donation info', 'give')}
                infoCardBadgeLabel={isRecurringDonation ? __('Recurring', 'give') : __('One-time', 'give')}
            />        
        </OverviewPanel>
    );
}
