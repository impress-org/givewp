import React from 'react';
import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import DonationBreakdown from './DonationBreakdown';
import BillingInformation from './BillingInformation';
import ReceiptActions from './ReceiptActions';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export default function DonationReceipt() {
  const billingInfo = {
    name: 'John Doe',
    email: 'johndoe25@example.com',
    address: [
      '1234 Elm Street',
      'Apt 567',
      'Springfield, CA 90210',
      __('United States', 'give')
    ]
  };
  
  return (
    <OverviewPanel>
      <aside
        className={styles.receipt}
        role="region"
        aria-labelledby="donation-details-title"
      >
        <div className={styles.content}>
          <header className={styles.header} role="banner">
            <h2 className={styles.title} id="donation-details-title">{__('Fundraising Form', 'give')}</h2>
            <p>{__('Below is a detailed breakdown of this donation.', 'give')}</p>
          </header>

          <div className={styles.sections}>
            <section className={styles.rows} aria-label={__('Donation breakdown', 'give')}>
              <DonationBreakdown />
            </section>

            <section className={styles.address} aria-labelledby="billing-information">
              <h3 id="billing-information">{__('Billing information', 'give')}</h3>
              <BillingInformation {...billingInfo} />
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
