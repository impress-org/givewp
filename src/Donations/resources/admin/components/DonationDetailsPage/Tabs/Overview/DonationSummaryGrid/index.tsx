import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import PaymentMethodIcon from './PaymentMethodIcon';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import ExternalLinkIcon, { InfoIcon } from './icon';
import type {Donation} from '@givewp/donations/admin/components/types';
import { useDonorEntityRecord } from '@givewp/donors/utils';
import { useCampaignEntityRecord } from '@givewp/campaigns/utils';
import Spinner from '@givewp/src/Admin/components/Spinner';
import Grid, { GridCard } from '@givewp/src/Admin/components/Grid';

import styles from './styles.module.scss';

/**
 * @since 4.6.0
 */
export type DonationSummaryGridProps = {
    donation: Donation;
};

/**
 * @since 4.8.0 export function for SubscriptionSummaryGrid & add GridCard component
 * @since 4.6.0
 */
export function CampaignCard({donation}: {donation: Donation}) {
    const {campaign, hasResolved: hasResolvedCampaign} = useCampaignEntityRecord(donation?.campaignId);

    return (
        <GridCard heading={__('Campaign name', 'give')} headingId="campaign-name">
            {!hasResolvedCampaign && <Spinner />}
            {hasResolvedCampaign && campaign && (
                <a
                    href={`edit.php?post_type=give_forms&page=give-campaigns&id=${campaign?.id}&tab=overview`}
                    className={styles.campaignLink}
                >
                    {campaign?.title}
                </a>
            )}
        </GridCard>
    );
}

/**
 * @since 4.8.0 export function for SubscriptionSummaryGrid & add GridCard component
 * @since 4.6.0
 */
export function DonorCard({donation}: {donation: Donation}) {
    const {record: donor, hasResolved: hasResolvedDonor, isResolving: isResolvingDonor} = useDonorEntityRecord(donation?.donorId);

    return (
        <GridCard heading={__('Associated donor', 'give')} headingId="donor">
         {!hasResolvedDonor && <Spinner />}
         {hasResolvedDonor && (
            <>
                {donor ? (
                    <>
                        <a className={styles.donorLink} href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`}>
                            {donor.name}
                        </a>
                        <p>{donor.email}</p>
                    </>
                ) : (
                    <p>{__('No donor associated with this donation', 'give')}</p>
                )}
            </>
         )}
        </GridCard>
    );
}

/**
 * @since 4.8.0
 */
export function GatewayNotice() {
    return (
        <div className={styles.notice}>
            <div className={styles.noticeIcon}>
                <InfoIcon />
            </div>
            <div className={styles.noticeContent}>
                <strong className={styles.noticeTitle}>{__('Gateway Details Unavailable', 'give')}</strong>
                <p className={styles.noticeDescription}>
                    {__('This donation\'s gateway is not active on your site. Install the matching payment gateway to see full details.', 'give')}
                </p>
            </div>
        </div>
    );
}

/**
 * @unrleased add Grid components & variables
 * @since 4.6.0
 */
export default function DonationSummaryGrid({
    donation,
}: DonationSummaryGridProps) {
     const isRecurringDonation = !!donation?.subscriptionId;
     const badgeLabel = isRecurringDonation ? __('Recurring', 'give') : __('One-time', 'give');
     const subscriptionPageUrl = donation?.subscriptionId ? `edit.php?post_type=give_forms&page=give-subscriptions&id=${donation?.subscriptionId}` : null;
     const createdAt = donation?.createdAt?.date;
     const paymentMethod = donation?.gateway?.id;
     const gatewayLabel = donation?.gateway?.label;
     const gatewayLink = donation?.gateway?.transactionUrl;

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>

          <Grid ariaLabel={__('Donation summary', 'give')}>
                {/* Campaign Name */}
                <CampaignCard donation={donation} />

                {/* Donation Info */}
                <GridCard heading={__('Donation info', 'give')} headingId="donation-info">
                    <time className={styles.date} dateTime={createdAt}>
                        {formatTimestamp(createdAt, true)}
                    </time>
                    <div className={styles.donationType}>
                        <span className={styles.badge} aria-label={badgeLabel}>
                            {badgeLabel}
                        </span>
                        {isRecurringDonation && (
                            <a
                                className={styles.gatewayLink}
                                href={subscriptionPageUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {__('View details', 'give')}
                                <ExternalLinkIcon />
                            </a>
                        )}
                    </div>
                </GridCard>

                {/* Associated Donor */}
                <DonorCard donation={donation} />

                {/* Gateway Info */}
                <GridCard heading={__('Gateway', 'give')} headingId="gateway">
                    {!paymentMethod ? <GatewayNotice /> : (
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
                                    {__('View donation on gateway', 'give')}
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
