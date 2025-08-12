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

/**
 * @since 4.6.0
 */
export type DonationSummaryGridProps = {
    donation: Donation;
};

/**
 * @since 4.6.0
 */
function CampaignCard({donation}: {donation: Donation}) {
    const {campaign, hasResolved: hasResolvedCampaign} = useCampaignEntityRecord(donation?.campaignId);

    return (
        <div
            className={classnames(styles.card, styles.campaignCard)}
            role="region"
            aria-labelledby="campaign-name-label"
        >
            <h3 id="campaign-name-label">{__('Campaign name', 'give')}</h3>
            {!hasResolvedCampaign && <Spinner />}
            {hasResolvedCampaign && campaign && (
                <a
                    href={`edit.php?post_type=give_forms&page=give-campaigns&id=${campaign?.id}&tab=overview`}
                    className={styles.campaignLink}
                >
                    {campaign?.title}
                </a>
            )}
        </div>
    );
}

/**
 * @since 4.6.0
 */
function DonorCard({donation}: {donation: Donation}) {
    const {record: donor, hasResolved: hasResolvedDonor} = useDonorEntityRecord(donation?.donorId);

    return (
        <div className={classnames(styles.card, styles.donorCard)} role="region" aria-labelledby="donor-label">
         <h3 id="donor-label">{__('Associated donor', 'give')}</h3>
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
     </div>
    );
}

/**
 * @since 4.6.0
 */
export default function DonationSummaryGrid({
    donation,
}: DonationSummaryGridProps) {
     const subscriptionPageUrl = donation?.subscriptionId ? `edit.php?post_type=give_forms&page=give-subscriptions&view=overview&id=${donation?.subscriptionId}` : null;
     const isRecurringDonation = !!donation?.subscriptionId;
     const donationTypeDisplay = isRecurringDonation ? __('Recurring', 'give') : __('One-time', 'give');

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>

            <div className={styles.container} role="group" aria-label={__('Donation summary', 'give')}>
                {/* Campaign Name */}
                <CampaignCard donation={donation} />

                {/* Donation Info */}
                <div className={styles.card} role="region" aria-labelledby="donation-info-label">
                    <h3 id="donation-info-label">{__('Donation info', 'give')}</h3>
                    <time className={styles.date} dateTime={donation.createdAt?.date}>
                        {formatTimestamp(donation.createdAt?.date, true)}
                    </time>
                    <div className={styles.donationType}>
                        <span className={styles.badge} aria-label={donationTypeDisplay}>
                            {donation.type && donationTypeDisplay}
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

                </div>

                {/* Associated Donor */}
                <DonorCard donation={donation} />

                {/* Gateway Info */}
                <div className={styles.card} role="region" aria-labelledby="gateway-label">
                    <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
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
                </div>
            </div>
        </OverviewPanel>
    );
}
