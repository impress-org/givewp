import StatWidget from "@givewp/admin/components/StatWidget";
import { __ } from "@wordpress/i18n";
import styles from "./ListTable.module.scss";

/**
 * @unreleased
 */
export default function DonationTableStats() {
     const hasRecurringDonationsAddon = window.GiveDonations.recurringDonations;

    return (
        <section className={styles.tableStatsContainer} role="region" aria-label={__('Donation statistics', 'give')}>
            <StatWidget
                className={styles.tableStatWidget}
                label={__('Number of donations', 'give')}
                value={100}
            />
            <StatWidget
                className={styles.tableStatWidget}
                label={__('One-time donations', 'give')}
                value={100}
            />
            <StatWidget
                className={styles.tableStatWidget}
                label={__('Recurring donations', 'give')}
                value={hasRecurringDonationsAddon ? 100 : 0}
                inActive={!hasRecurringDonationsAddon}
                href={'https://givewp.com/addons/recurring-donations/'}
                toolTipDescription={__('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give')}
            />
        </section>
    );
}