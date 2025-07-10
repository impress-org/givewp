
import { __ } from '@wordpress/i18n';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import DonationBreakdown from './DonationBreakdown';
import BillingInformation from './BillingInformation';
import ReceiptActions from './ReceiptActions';
import styles from './styles.module.scss';
import { Donation } from '@givewp/donations/admin/components/types';

/**
 * @unreleased
 */
export default function DonationReceipt({ donation }: { donation: Donation }) {
  const billingInfo = {
    name: donation?.firstName + ' ' + donation?.lastName,
    email: donation?.email,
    address: {
      country: donation?.billingAddress?.country,
      address1: donation?.billingAddress?.address1,
      address2: donation?.billingAddress?.address2,
      city: donation?.billingAddress?.city,
      state: donation?.billingAddress?.state,
      zip: donation?.billingAddress?.zip,
    },
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
              <DonationBreakdown donation={donation} />
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
