import { __ } from '@wordpress/i18n';
import OverviewPanel from "@givewp/src/Admin/components/OverviewPanel";
import PaymentDetails from '@givewp/admin/components/PaymentDetails';
import { HourGlassIcon, ClockIcon } from './Icons';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { Donation } from '@givewp/donations/admin/components/types';
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
    const infoCardBadgeLabel = isOngoing ? <><ClockIcon />{__('Unlimited', 'give')}</> : <><HourGlassIcon />{__('Limited', 'give')}</>;
  
    return (
        <OverviewPanel>
            <h2 id="subscription-details-grid-title" className={'sr-only'}>
                {__('Subscription Details', 'give')}
            </h2>
            <PaymentDetails
                donation={donation}
                gatewayLinkLabel={__('View subscription on gateway', 'give')}
                isSubscriptionPage={true}
                subscriptionRenewalDate={subscription?.renewsAt?.date}
                infoCardTitle={__('Next payment`', 'give')}
                infoCardBadgeLabel={infoCardBadgeLabel}
                infoCardClassName={isOngoing ? styles.unlimited : styles.limited}
            />   
        </OverviewPanel>
    );
}
