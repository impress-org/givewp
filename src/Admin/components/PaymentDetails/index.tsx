import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import PaymentMethodIcon from './PaymentMethodIcon';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import ExternalLinkIcon from './Icon';
import type {Donation} from '@givewp/donations/admin/components/types';
import { useDonorEntityRecord } from '@givewp/donors/utils';
import { useCampaignEntityRecord } from '@givewp/campaigns/utils';
import Spinner from '@givewp/src/Admin/components/Spinner';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type DetailsGridProps = {
    children: React.ReactNode;
};

/**
 * @unreleased
 */
export function DetailsGrid({children}: DetailsGridProps) {
    return (
        <div className={styles.container} role="group" aria-label={__('Payment details', 'give')}>
            {children}
        </div>
    );
}

/**
 * @unreleased
 */
type CampaignCardProps = {
    donation: Donation;
};

/**
 * @unreleased
 */
export function CampaignCard({donation}: CampaignCardProps) {
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
type DonorCardProps = {
    donation: Donation;
};

/**
 * @unreleased
 */
export function DonorCard({donation}: DonorCardProps) {
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
type GatewayCardProps = {
    isLoading: boolean;
    href?: string;
    gatewayLabel: string;
    linkLabel?: string;
    gatewayId: string;
};

/**
 * @unreleased
 */
export function GatewayCard({isLoading, href, gatewayLabel, linkLabel, gatewayId}: GatewayCardProps) {
    return (
        <div className={styles.card} role="region" aria-labelledby="gateway-label">
            {isLoading && <Spinner />}
            {!isLoading && (
                <>
                    <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
                    <strong className={styles.paymentMethod}>
                        <PaymentMethodIcon paymentMethod={gatewayId} />
                        {gatewayLabel}
                    </strong>
                </>
            )}

            {href && linkLabel && (
                <a
                    className={styles.gatewayLink}
                    href={href}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {linkLabel}
                    <ExternalLinkIcon />
                </a>
            )}
        </div>
    );
}

type PaymentCardProps = {
    children?: React.ReactNode;
    isLoading: boolean;
    dateTime: string;
    title: string;
    badgeLabel: string | React.ReactNode;
    className: string;
};

/**
 * @unreleased
 */
export function PaymentCard({children, isLoading, dateTime, title, badgeLabel, className}: PaymentCardProps) {
    return (
        <div className={styles.card} role="region" aria-labelledby="donation-info-label">
        <h3 id="donation-info-title">
            {title}
        </h3>
        {isLoading && <Spinner />}

        {!isLoading && (
            <>
                <time className={styles.date} dateTime={dateTime}>
                    {formatTimestamp(dateTime, true)}
                </time>
                <div className={styles.donationType}>
                    <span className={classnames(styles.badge, className)}>
                        {badgeLabel}
                    </span>
                    {children && children}
                </div>
            </>
        )}
    </div>
    );
}