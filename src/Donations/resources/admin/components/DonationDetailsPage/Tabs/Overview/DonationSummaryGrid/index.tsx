import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import classnames from 'classnames';
import styles from './styles.module.scss';
import { formatTimestamp } from '@givewp/src/Admin/utils';

/**
 * @unreleased
 */
export type DonationSummaryGridProps = {
    campaignTitle: string;
    donorName: string;
    donorEmail: string;
    gatewayId: string;
    donationDate: string;
    donationType: string;
    donorId: number;
    campaignId: number;
};

/**
 * @unreleased
 */
export default function DonationSummaryGrid({
    campaignTitle,
    donorName,
    donorEmail,
    gatewayId,
    donationDate,
    donationType,
    donorId,
    campaignId,
}: DonationSummaryGridProps) {
    const donorPageUrl = `edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donorId}`;
    const campaignPageUrl = `edit.php?post_type=give_forms&page=give-campaigns&id=${campaignId}&tab=overview`;

    return (
        <OverviewPanel className={styles.overviewPanel}>
            <h2 id="donation-summary-grid-title" className={'sr-only'}>
                {__('Donation Details', 'give')}
            </h2>

            <div className={styles.container} role="group" aria-label={__('Donation summary', 'give')}>
                {/* Campaign Name */}
                <div className={classnames(styles.card, styles.campaignCard)} role="region" aria-labelledby="campaign-name-label">
                    <h3 id="campaign-name-label">{__('Campaign name', 'give')}</h3>
                    <a href={campaignPageUrl} className={styles.campaignLink}>
                        {campaignTitle}
                    </a>
                </div>

                {/* Donation Info */}
                <div className={styles.card} role="region" aria-labelledby="donation-info-label">
                    <h3 id="donation-info-label">{__('Donation info', 'give')}</h3>
                    <time className={styles.date} dateTime={donationDate}>
                        {formatTimestamp(donationDate, true)}
                    </time>
                    <span className={styles.badge} aria-label={__('Donation type: One-time', 'give')}>
                        {donationType}
                    </span>
                </div>

                {/* Associated Donor */}
                <div className={styles.card} role="region" aria-labelledby="donor-label">
                    <h3 id="donor-label">{__('Associated donor', 'give')}</h3>
                    <a className={styles.donorLink} href={donorPageUrl}>{donorName}</a>
                    <p>{donorEmail}</p>
                </div>

                {/* Gateway Info */}
                <div className={styles.card} role="region" aria-labelledby="gateway-label">
                    <h3 id="gateway-label">{__('Gateway', 'give')}</h3>
                    <strong>{gatewayId}</strong>
                    <a className={styles.gatewayLink} href={'#'} target="_blank" rel="noopener noreferrer">
                        {__('View donation on gateway', 'give')}
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M8.166 1.751c0-.322.261-.583.583-.583h3.5c.322 0 .584.261.584.583v3.5a.583.583 0 0 1-1.167 0V3.16l-3.67 3.67a.583.583 0 0 1-.826-.825l3.671-3.67H8.75a.583.583 0 0 1-.583-.584z"
                                fill="#2271B1"
                            />
                            <path
                                d="M4.525 2.335h1.308a.583.583 0 1 1 0 1.166H4.549c-.5 0-.839 0-1.102.022-.255.021-.386.059-.477.105-.22.112-.398.29-.51.51-.047.092-.085.222-.105.478-.022.263-.022.602-.022 1.102v3.733c0 .5 0 .84.022 1.102.02.256.058.387.105.478.112.22.29.398.51.51.09.046.222.084.477.105.263.021.603.022 1.102.022h3.734c.5 0 .839 0 1.102-.022.255-.02.386-.059.477-.105.22-.112.398-.29.51-.51.047-.091.085-.222.105-.478.022-.262.022-.602.022-1.102V8.168a.583.583 0 0 1 1.167 0v1.307c0 .47 0 .857-.026 1.173-.027.328-.084.63-.228.913-.224.439-.581.796-1.02 1.02-.283.144-.585.201-.912.228-.316.026-.704.026-1.173.026H4.525c-.47 0-.857 0-1.173-.026-.327-.027-.629-.084-.912-.229a2.333 2.333 0 0 1-1.02-1.02c-.144-.283-.201-.584-.228-.912-.026-.316-.026-.703-.026-1.173V5.694c0-.47 0-.857.026-1.173.027-.328.084-.63.228-.912.224-.44.581-.796 1.02-1.02.283-.144.585-.202.912-.229.316-.025.704-.025 1.173-.025z"
                                fill="#2271B1"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </OverviewPanel>
    );
}