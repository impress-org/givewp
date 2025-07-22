
import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import DonationBreakdown from './DonationBreakdown';
import BillingInformation from './BillingInformation';
import ReceiptActions from './ReceiptActions';
import styles from './styles.module.scss';
import type { Donation } from '@givewp/donations/admin/components/types';

/**
 * @since 4.6.0
 */
export default function DonationReceipt({ donation }: { donation: Donation }) {
  return (
    <OverviewPanel>
      <aside
        className={styles.receipt}
        role="region"
        aria-labelledby="donation-details-title"
      >
        <div className={styles.content}>
          <header className={styles.header} role="banner">
            <h2 className={styles.title} id="donation-details-title">{donation?.formTitle}</h2>
            <p>{__('Below is a detailed breakdown of this donation.', 'give')}</p>
          </header>

          <div className={styles.sections}>
            <section className={styles.rows} aria-label={__('Donation breakdown', 'give')}>
              <DonationBreakdown donation={donation} />
            </section>

            <section className={styles.address} aria-labelledby="billing-information">
              <h3 id="billing-information">{__('Billing information', 'give')}</h3>
              <BillingInformation name={`${donation?.firstName} ${donation?.lastName}`.trim()} email={donation?.email} address={donation?.billingAddress} />
            </section>
          </div>
        </div>

        <nav className={styles.actions} aria-label={__('Receipt actions', 'give')}>
          <ReceiptActions />
        </nav>
      </aside>
    </OverviewPanel>
  );
}
