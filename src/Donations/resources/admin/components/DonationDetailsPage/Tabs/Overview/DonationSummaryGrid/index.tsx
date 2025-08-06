import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import PaymentMethodIcon from './PaymentMethodIcon';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import styles from './styles.module.scss';
import ExternalLinkIcon from './icon';
import type {Donation} from '@givewp/donations/admin/components/types';
import { useDonorEntityRecord } from '@givewp/donors/utils';
import { useCampaignEntityRecord } from '@givewp/campaigns/utils';
import Spinner from '@givewp/src/Admin/components/Spinner';
import Grid, { GridCard } from '@givewp/src/Admin/components/Grid';

/**
 * @since 4.6.0
 */
export type DonationSummaryGridProps = {
    donation: Donation;
};

/**
 * @since 4.6.0
 */
export function CampaignCard({donation}: {donation: Donation}) {
    const {campaign, hasResolved: hasResolvedCampaign} = useCampaignEntityRecord(donation?.campaignId);

    return (
        <GridCard className={classnames(styles.card, styles.campaignCard)} heading={__('Campaign name', 'give')} headingId="campaign-name">
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
 * @since 4.6.0
 */
export function DonorCard({donation}: {donation: Donation}) {
    const {record: donor, hasResolved: hasResolvedDonor} = useDonorEntityRecord(donation?.donorId);

    return (
        <GridCard className={classnames(styles.card, styles.donorCard)} heading={__('Associated donor', 'give')} headingId="donor">
         {!hasResolvedDonor && <Spinner />}
         {hasResolvedDonor && (
            <>
                <a className={styles.donorLink} href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`}>
                    {donor.name}
                </a>
                <p>{donor.email}</p>
            </>
         )}
        </GridCard>
    );
}

/**
 * @since 4.6.0
 */
export default function DonationPaymentGrid({
    donation,
}: DonationSummaryGridProps) {
     const subscriptionPageUrl = donation?.subscriptionId ? `edit.php?post_type=give_forms&page=give-subscriptions&view=overview&id=${donation?.subscriptionId}` : null;
     const isRecurringDonation = !!donation?.subscriptionId;
     const badgeLabel = isRecurringDonation ? __('Recurring', 'give') : __('One-time', 'give');

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>

          <Grid ariaLabel={__('Donation summary', 'give')}>
                {/* Campaign Name */}
                <CampaignCard donation={donation} />

                {/* Donation Info */}
                <GridCard className={styles.card} heading={__('Donation info', 'give')} headingId="donation-info">
                    <time className={styles.date} dateTime={donation.createdAt?.date}>
                        {formatTimestamp(donation.createdAt?.date, true)}
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
                <GridCard className={styles.card} heading={__('Gateway', 'give')} headingId="gateway">
                    <strong className={styles.paymentMethod}>
                        <PaymentMethodIcon paymentMethod={donation.gateway.id} />
                        {donation.gateway.label}
                    </strong>
                    {donation.gateway.transactionUrl && (
                        <a
                            className={styles.gatewayLink}
                            href={donation.gateway.transactionUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {__('View donation on gateway', 'give')}
                            <ExternalLinkIcon />
                        </a>
                    )}
                </GridCard>
            </Grid>  
        </OverviewPanel>
    );
}
