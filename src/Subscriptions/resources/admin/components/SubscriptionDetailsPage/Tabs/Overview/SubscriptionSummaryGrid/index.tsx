import {Grid, GridCard, OverviewPanel, Spinner} from '@givewp/admin/components';
import {formatTimestamp} from '@givewp/admin/utils';
import {GatewayNotice} from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid';
import PaymentMethodIcon from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid/PaymentMethodIcon';
import ExternalLinkIcon from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid/icon';
import {Subscription} from '@givewp/subscriptions/admin/components/types';
import {__} from '@wordpress/i18n';
import classnames from 'classnames';
import {ClockIcon, HourGlassIcon} from './Icons';
import DonorCard from './DonorCard';
import CampaignCard from './CampaignCard';
import styles from './styles.module.scss';
import { getSubscriptionEmbeds } from '@givewp/subscriptions/common';

/**
 * @since 4.10.0 removed donation
 * @since 4.8.0
 */
type SubscriptionSummaryGridProps = {
    subscription: Subscription;
    isLoading: boolean;
};

/**
 * @since 4.10.0 removed donation dependency
 * @since 4.8.0
 */
export default function SubscriptionSummaryGrid({subscription, isLoading}: SubscriptionSummaryGridProps) {
    const { campaign, donor } = getSubscriptionEmbeds(subscription);
    const isOngoing = subscription?.installments === 0;
    const badgeLabel = isOngoing ? (
        <>
            <ClockIcon />
            {__('Unlimited', 'give')}
        </>
    ) : (
        <>
            <HourGlassIcon />
            {__('Limited', 'give')}
        </>
    );
    const renewsAt = subscription?.renewsAt;
    const paymentMethodId = subscription?.gatewayId;
    const hasPaymentMethodDetails = subscription?.gateway?.id;
    const gatewayLabel = subscription?.gateway?.label;
    const gatewayLink = subscription?.gateway?.subscriptionUrl;

    return (
           <OverviewPanel className={styles.overviewPanel}>
            <h2 id="subscription-details-grid-title" className={'sr-only'}>
                {__('Subscription Details', 'give')}
            </h2>
            <Grid ariaLabel={__('Subscription details', 'give')}>
                {/* Campaign Name */}
                <CampaignCard campaign={campaign} />

                {/* Next Payment */}
                <GridCard className={styles.card} heading={__('Next payment', 'give')} headingId="next-payment">
                    {isLoading && <Spinner />}
                    {!isLoading && (
                        <>
                            <time className={styles.date} dateTime={renewsAt}>
                                {formatTimestamp(renewsAt, true)}
                            </time>
                            <div className={styles.donationType}>
                                <span
                                    className={classnames(styles.badge, {
                                        [styles.unlimited]: isOngoing,
                                        [styles.limited]: !isOngoing,
                                    })}
                                    aria-label={isOngoing ? __('Unlimited', 'give') : __('Limited', 'give')}
                                >
                                    {badgeLabel}
                                </span>
                            </div>
                        </>
                    )}
                </GridCard>

                {/* Associated Donor */}
                <DonorCard donor={donor} />

                {/* Gateway Info */}
                <GridCard heading={__('Gateway', 'give')} headingId="gateway">
                    {isLoading ? (
                        <Spinner />
                    ) : !hasPaymentMethodDetails ? (
                        <GatewayNotice />
                    ) : (
                        <>
                            <strong className={styles.paymentMethod}>
                                <PaymentMethodIcon paymentMethod={paymentMethodId} />
                                {gatewayLabel}
                            </strong>
                            {gatewayLink && (
                                <a
                                    className={styles.gatewayLink}
                                    href={gatewayLink}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {__('View subscription on gateway', 'give')}
                                    <ExternalLinkIcon />
                                </a>
                            )}
                        </>
                    )}
                </GridCard>
            </Grid>
        </OverviewPanel>
    );
}
