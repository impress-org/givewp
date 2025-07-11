import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import EventLabel from './EventsLabel';
import CurrencyBreakdownArrowIcon from './icon';
import styles from './styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { Donation } from '@givewp/donations/admin/components/types';
import { useNormalizeDonation } from '@givewp/donations/hooks/useNormalizeDonation';
import { amountFormatter } from '@givewp/src/Admin/utils';

/**
 * @unreleased
 */
export default function DonationBreakdown({ donation }: { donation: Donation }) {
  const { isFeeRecoveryEnabled, currency: defaultCurrency } = getDonationOptionsWindowData();
  const baseCurrencyFormatter = amountFormatter(defaultCurrency, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    roundingMode: 'trunc',
  });

  const baseCurrencyAmount = (donation?.amount?.value ?? 0) / Number(donation?.exchangeRate ?? 1);
  const {formatter, amount, intendedAmount, feeAmountRecovered, eventTicketAmount} = useNormalizeDonation(donation);

  const showFeeRecoveredRow = isFeeRecoveryEnabled;
  const showEventTicketRow = false; // Placeholder for future event ticket logic
  const showCurrencyBreakdownRow = donation?.amount?.currency !== defaultCurrency;

  // Placeholder for event ticket details, to be replaced with real data
  const eventTicketDetailsArray: any[] = [];

  return (
      <div className={styles.rowContainer}>
          <Row
              className={styles.donationRow}
              label={__('Donation amount', 'give')}
              value={formatter.format(intendedAmount)}
          />

          {showEventTicketRow && (
              <Row
                  className={styles.donationRow}
                  label={<EventLabel events={eventTicketDetailsArray} />}
                  value={formatter.format(eventTicketAmount)}
              />
          )}

          {showFeeRecoveredRow && (
              <Row
                  className={styles.donationRow}
                  label={__('Fee Recovered', 'give')}
                  value={formatter.format(feeAmountRecovered)}
              />
          )}

          <Row
              className={styles.totalRow}
              label={<strong>{__('Total', 'give')}</strong>}
              value={<strong>{formatter.format(amount)}</strong>}
          />

          {showCurrencyBreakdownRow && (
              <Row
                  className={styles.currencyRow}
                  label={__('Currency breakdown', 'give')}
                  value={
                      <>
                          {baseCurrencyFormatter.format(baseCurrencyAmount)}
                          <CurrencyBreakdownArrowIcon />
                          {formatter.format(amount)}
                      </>
                  }
              />
          )}
      </div>
  );
}

/**
 * @unreleased
 */
type RowProps = {
  label?: React.ReactNode;
  value?: React.ReactNode;
  children?: React.ReactNode;
  className?: string;
};

/**
 * @unreleased
 */
function Row({ label, value, children, className }: RowProps) {
  return (
    <div className={classnames(styles.row, className)}>
      <dt className={styles.label}>
        {label}
        {children}
      </dt>
      <dd className={styles.value}>
        {value}
      </dd>
    </div>
  );
}
