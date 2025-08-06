import { __ } from '@wordpress/i18n';
import OverviewPanel from "@givewp/src/Admin/components/OverviewPanel";
import { HourGlassIcon, ClockIcon } from './Icons';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { Donation } from '@givewp/donations/admin/components/types';
import { 
    DetailsGrid as PaymentDetailsGrid,
    CampaignCard,
    PaymentCard,
    DonorCard,
    GatewayCard
} from '@givewp/src/Admin/components/PaymentDetails';

import styles from './styles.module.scss';

type SubscriptionDetailsProps = {
    subscription: Subscription;
    donation?: Donation;
}

/**
 * @unreleased
 */
export default function SubscriptionPaymentDetails({subscription, donation}: SubscriptionDetailsProps) {    
    const isOngoing = subscription?.installments === 0;
    const badgeLabel = isOngoing ? <><ClockIcon />{__('Unlimited', 'give')}</> : <><HourGlassIcon />{__('Limited', 'give')}</>;
  
    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="subscription-details-grid-title" className={'sr-only'}>
                {__('Subscription Details', 'give')}
            </h2>
            <PaymentDetailsGrid>
                <CampaignCard donation={donation} />
                <PaymentCard 
                    isLoading={false} 
                    dateTime={subscription?.createdAt?.date} 
                    title={__('Next payment', 'give')} 
                    badgeLabel={badgeLabel} 
                    className={isOngoing ? styles.unlimited : styles.limited}
               />
               
                <DonorCard donation={donation} />
                <GatewayCard 
                    isLoading={!donation} 
                    href={donation?.gateway?.transactionUrl} 
                    gatewayLabel={subscription?.gateway?.label} 
                    linkLabel={__('View subscription on gateway', 'give')} 
                    gatewayId={subscription?.gateway?.id} 
                />
            </PaymentDetailsGrid>
        </OverviewPanel>
    );
}
