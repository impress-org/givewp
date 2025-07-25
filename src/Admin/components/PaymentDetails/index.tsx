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


/**
 * @unreleased
 */
function CampaignCard({record}: {record: Donation}) {
    const {campaign, hasResolved: hasResolvedCampaign} = useCampaignEntityRecord(record?.campaignId);

    return (
        <div
            className={classnames(styles.card, styles.campaignCard)}
            role="region"
            aria-labelledby="campaign-name-label"
        >
            <h3 id="campaign-name-label">{__('Campaign name', 'give')}</h3>
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
function DonorCard({record}: {record: Donation}) {
    const {record: donor, hasResolved: hasResolvedDonor} = useDonorEntityRecord(record?.donorId);

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
type DetailsGridProps = {
  record: any;
  isSubscriptionPage?: boolean;
  gatewayLinkLabel?: string;
  subscriptionPageUrl?: string;
  infoCardTitle?: string;
  infoCardBadgeLabel?: string | React.ReactNode;
  infoCardClassName?: string;
}

/**
 * @unreleased
 * Generic donation | subscription details.
 */
export default function PaymentDetails({
  record,
  isSubscriptionPage = false,
  gatewayLinkLabel = __('View donation on gateway', 'give'),
  subscriptionPageUrl,
  infoCardTitle,
  infoCardBadgeLabel,
  infoCardClassName,
}: DetailsGridProps) {
    return (
        <div className={styles.container} role="group" aria-label={__('Donation summary', 'give')}>
            {/* Campaign Name */}
            <CampaignCard record={record} />

            {/* Donation Info or Next Payment */}
            <div className={styles.card} role="region" aria-labelledby="donation-info-label">
                <h3 id="donation-info-title">
                    {infoCardTitle}
                </h3>

                <time className={styles.date} dateTime={record.createdAt?.date}>
                    {formatTimestamp(record.createdAt?.date, true)}
                </time>

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
            </div>

            {/* Associated Donor */}
            <DonorCard record={record} />

            {/* Gateway Info */}
            <div className={styles.card} role="region" aria-labelledby="gateway-label">
                <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
                <strong className={styles.paymentMethod}>
                    <PaymentMethodIcon paymentMethod={record.gateway?.id} />
                    {record.gateway?.label}
                </strong>

                {record.gateway?.transactionUrl && (
                    <a
                        className={styles.gatewayLink}
                        href={record.gateway?.transactionUrl}
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