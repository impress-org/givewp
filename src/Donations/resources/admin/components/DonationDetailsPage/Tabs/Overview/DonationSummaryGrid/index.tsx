import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import PaymentMethodIcon from './PaymentMethodIcon';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import styles from './styles.module.scss';
import ExternalLinkIcon from './icon';

/**
 * @unreleased
 */
export type DonationSummaryGridProps = {
    campaign: {
        id: number;
        title: string;
    };
    donor: {
        id: number;
        name: string;
        email: string;
    };
    donation: {
        amount: string;
        feeAmountRecovered: string | number;
        status: string;
        date: string;
        paymentMethod: string;
        mode: string;
        gatewayViewUrl?: string | null;
    };
    details?: Array<{ label: string; [key: string]: any }>;
    donationType: string;
    subscriptionId: number;
};

/**
 * @unreleased
 */
export default function DonationSummaryGrid({
    campaign,
    donor,
    donation,
    donationType,
    details,
    subscriptionId
}: DonationSummaryGridProps) {
    const donorPageUrl = `edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`;
    const campaignPageUrl = `edit.php?post_type=give_forms&page=give-campaigns&id=${campaign.id}&tab=overview`;
    const subscriptionPageUrl = `edit.php?post_type=give_forms&page=give-subscriptions&view=overview&id=${subscriptionId}`;
    const donationTypeDisplay = donationType === 'single' ? __('One-time', 'give') : __('Repeat', 'give');
    const getPaymentMethodValue = (details, label) => {
        const found = details?.find(detail => detail.label === label);
        return found?.value;
    };
    const paymentMethod = getPaymentMethodValue(details, 'Payment Method');

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>

            <div className={styles.container} role="group" aria-label={__('Donation summary', 'give')}>
                {/* Campaign Name */}
                <div
                    className={classnames(styles.card, styles.campaignCard)}
                    role="region"
                    aria-labelledby="campaign-name-label"
                >
                    <h3 id="campaign-name-label">{__('Campaign name', 'give')}</h3>
                    <a href={campaignPageUrl} className={styles.campaignLink}>
                        {campaign.title}
                    </a>
                </div>

                {/* Donation Info */}
                <div className={styles.card} role="region" aria-labelledby="donation-info-label">
                    <h3 id="donation-info-label">{__('Donation info', 'give')}</h3>
                    <time className={styles.date} dateTime={donation.date}>
                        {formatTimestamp(donation.date, true)}
                    </time>
                    <div className={styles.donationType}>
                        <span className={styles.badge} aria-label={donationTypeDisplay}>
                            {donationType && donationTypeDisplay}
                        </span>
                        {donationType !== 'single' && (
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
                <div className={classnames(styles.card, styles.donorCard)} role="region" aria-labelledby="donor-label">
                    <h3 id="donor-label">{__('Associated donor', 'give')}</h3>
                    <a className={styles.donorLink} href={donorPageUrl}>
                        {donor.name}
                    </a>
                    <p>{donor.email}</p>
                </div>

                {/* Gateway Info */}
                <div className={styles.card} role="region" aria-labelledby="gateway-label">
                    <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
                    <strong className={styles.paymentMethod}>
                        <PaymentMethodIcon paymentMethod={donation?.paymentMethod} />
                        {paymentMethod}
                    </strong>
                    {donation.gatewayViewUrl && (
                        <a
                            className={styles.gatewayLink}
                            href={donation.gatewayViewUrl}
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