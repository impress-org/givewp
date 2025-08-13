import { __ } from '@wordpress/i18n';
import OverviewPanel from "@givewp/admin/components/OverviewPanel";
import Grid, { GridCard } from '@givewp/admin/components/Grid';
import { CampaignCard, DonorCard } from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid';
import Spinner from '@givewp/admin/components/Spinner';
import PaymentMethodIcon from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid/PaymentMethodIcon';
import ExternalLinkIcon from '@givewp/donations/admin/components/DonationDetailsPage/Tabs/Overview/DonationSummaryGrid/icon';
import { HourGlassIcon, ClockIcon } from './Icons';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { Donation } from '@givewp/donations/admin/components/types';
import { formatTimestamp } from '@givewp/admin/utils';
import classnames from 'classnames';

import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionDetailsProps = {
    subscription: Subscription;
    donation?: Donation;
    isLoading: boolean;
};

/**
 * @unreleased
 */
export default function SubscriptionSummaryGrid({subscription, donation, isLoading}: SubscriptionDetailsProps) {    
    const isOngoing = subscription?.installments === 0;
    const badgeLabel = isOngoing ? <><ClockIcon />{__('Unlimited', 'give')}</> : <><HourGlassIcon />{__('Limited', 'give')}</>;
    const renewsAt = subscription?.renewsAt?.date;
    const paymentMethod = subscription?.gateway?.id || donation?.gateway?.id;
    const gatewayLabel = subscription?.gateway?.label || donation?.gateway?.label;
    const gatewayLink = subscription?.gateway?.subscriptionUrl || donation?.gateway?.transactionUrl;

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="subscription-details-grid-title" className={'sr-only'}>
                {__('Subscription Details', 'give')}
            </h2>
            <Grid ariaLabel={__('Subscription details', 'give')}>
                {/* Campaign Name */}
                <CampaignCard donation={donation} />

                {/* Next Payment */}
                <GridCard className={styles.card} heading={__('Next payment', 'give')} headingId="next-payment">
                    {isLoading && <Spinner />}
                    {!isLoading && (
                        <>
                            <time className={styles.date} dateTime={renewsAt}>
                                {formatTimestamp(renewsAt, true)}
                            </time>
                            <div className={styles.donationType}>
                                <span className={classnames(styles.badge, {
                                    [styles.unlimited]: isOngoing,
                                    [styles.limited]: !isOngoing,
                                })} aria-label={isOngoing ? __('Unlimited', 'give') : __('Limited', 'give')}
                                >
                                    {badgeLabel}
                                </span>
                            </div>
                        </>
                    )}
                </GridCard>

                {/* Associated Donor */}
                <DonorCard donation={donation} />

                {/* Gateway Info */}
                <GridCard heading={__('Gateway', 'give')} headingId="gateway">
                    {isLoading && <Spinner />}
                    {!isLoading && (
                        <>
                            <strong className={styles.paymentMethod}>
                                <PaymentMethodIcon paymentMethod={paymentMethod} />
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
