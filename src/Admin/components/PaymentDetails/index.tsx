import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import PaymentMethodIcon from './PaymentMethodIcon';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import ExternalLinkIcon from './Icon';
import type {Donation} from '@givewp/donations/admin/components/types';
import { useDonorEntityRecord } from '@givewp/donors/utils';
import { useCampaignEntityRecord } from '@givewp/campaigns/utils';
import Spinner from '@givewp/src/Admin/components/Spinner';

import styles from './styles.module.scss';
import { Subscription } from '@givewp/subscriptions/admin/components/types';


/**
 * @unreleased
 */
function CampaignCard({donation}: {donation: Donation}) {
    const {campaign, hasResolved: hasResolvedCampaign} = useCampaignEntityRecord(donation?.campaignId);

    return (
        <div
            className={classnames(styles.card, styles.campaignCard)}
            role="region"
            aria-labelledby="campaign-name-label"
        >
            <h3 id="campaign-name-label">{__('Campaign details', 'give')}</h3>
            {!hasResolvedCampaign && <Spinner />}
            {hasResolvedCampaign && (
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
 * @unreleased
 */
function DonorCard({donation}: {donation: Donation}) {
    const {record: donor, hasResolved: hasResolvedDonor} = useDonorEntityRecord(donation?.donorId);

    return (
        <div className={classnames(styles.card, styles.donorCard)} role="region" aria-labelledby="donor-label">
         <h3 id="donor-label">{__('Associated donor', 'give')}</h3>
         {!hasResolvedDonor && <Spinner />}
         {hasResolvedDonor && (
            <>
                <a className={styles.donorLink} href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`}>
                    {donor?.name}
                </a>
                <p>{donor?.email}</p>
            </>
         )}
     </div>
    );
}

/**
 * @unreleased
 */
type PaymentDetailsProps = {
  donation: Donation;
  subscription?: Subscription;
  isSubscriptionPage?: boolean;
  gatewayLinkLabel?: string;
  subscriptionPageUrl?: string;
  subscriptionRenewalDate?: string;
  infoCardTitle?: string;
  infoCardBadgeLabel?: string | React.ReactNode;
  infoCardClassName?: string;
}

/**
 * @unreleased
 * Generic donation | subscription details.
 */
export default function PaymentDetails({
  donation,
  isSubscriptionPage = false,
  gatewayLinkLabel = __('View donation on gateway', 'give'),
  subscriptionPageUrl,
  subscriptionRenewalDate,
  infoCardTitle,
  infoCardBadgeLabel,
  infoCardClassName,
}: PaymentDetailsProps) {
    const gatewayTransactionUrl = donation?.gateway?.transactionUrl;
    const timestamp = isSubscriptionPage ? subscriptionRenewalDate : donation?.createdAt?.date;

    return (
        <div className={styles.container} role="group" aria-label={__('Donation summary', 'give')}>
            {/* Campaign Name */}
            <CampaignCard donation={donation} />

            {/* Donation Info or Next Payment */}
            <div className={styles.card} role="region" aria-labelledby="donation-info-label">
                <h3 id="donation-info-title">
                    {infoCardTitle}
                </h3>
                {!donation && <Spinner />}

                {donation && (
                    <time className={styles.date} dateTime={donation?.createdAt?.date}>
                        {formatTimestamp(timestamp, true)}
                    </time>
                )}

                {donation &&
                    <div className={styles.donationType}>
                        <span className={classnames(styles.badge, infoCardClassName)}>
                            {infoCardBadgeLabel}
                        </span>
                        {subscriptionPageUrl && !isSubscriptionPage && (
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
                }
            </div>

            {/* Associated Donor */}
            <DonorCard donation={donation} />

            {/* Gateway Info */}
            <div className={styles.card} role="region" aria-labelledby="gateway-label">
                {!donation && <Spinner />}
                {donation && (
                    <>
                        <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
                        <strong className={styles.paymentMethod}>
                            <PaymentMethodIcon paymentMethod={donation?.gateway?.id} />
                            {donation?.gateway?.label}
                        </strong>
                    </>
                )}

                {gatewayTransactionUrl && (
                    <a
                        className={styles.gatewayLink}
                        href={gatewayTransactionUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {gatewayLinkLabel}
                        <ExternalLinkIcon />
                    </a>
                )}
            </div>
        </div>
    );
}
