import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import styles from './styles.module.scss';
import type {Donation} from '@givewp/donations/admin/components/types';

import { 
    DetailsGrid as PaymentDetailsGrid,
    CampaignCard,
    PaymentCard,
    DonorCard,
    GatewayCard
} from '@givewp/src/Admin/components/PaymentDetails';
import ExternalLinkIcon from '@givewp/src/Admin/components/PaymentDetails/Icon';

/**
 * @since 4.6.0
 */
export type DonationPaymentDetailsProps = {
    donation: Donation;
};

/**
 * @unreleased
 * @since 4.6.0
 */
export default function DonationPaymentDetails({
    donation,
}: DonationPaymentDetailsProps) {
     const subscriptionPageUrl = donation?.subscriptionId ? `edit.php?post_type=give_forms&page=give-subscriptions&view=overview&id=${donation?.subscriptionId}` : null;
     const isRecurringDonation = !!donation?.subscriptionId;
     const donationTypeDisplay = isRecurringDonation ? __('Recurring', 'give') : __('One-time', 'give');

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>
            <PaymentDetailsGrid>
                <CampaignCard donation={donation} />
                <PaymentCard 
                    isLoading={false} 
                    dateTime={donation?.createdAt?.date} 
                    title={donationTypeDisplay} 
                    badgeLabel={donationTypeDisplay} 
                    className={styles.badge}
                >
                    {isRecurringDonation && 
                        <a
                            className={styles.gatewayLink}
                            href={subscriptionPageUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {__('View details', 'give')}
                            <ExternalLinkIcon />
                        </a>
                    }
                </PaymentCard>
                <DonorCard donation={donation} />
                <GatewayCard 
                    isLoading={!donation} 
                    href={donation?.gateway?.transactionUrl} 
                    gatewayLabel={donation?.gateway?.label} 
                    linkLabel={__('View donation on gateway', 'give')} 
                    gatewayId={donation?.gateway?.id} 
                />
            </PaymentDetailsGrid>

        </OverviewPanel>
    );
}
