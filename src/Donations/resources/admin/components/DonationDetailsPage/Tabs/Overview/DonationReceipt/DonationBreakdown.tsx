import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import EventLabel from './EventsLabel';
import CurrencyBreakdownArrowIcon from './icon';
import styles from './styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import type { Donation } from '@givewp/donations/admin/components/types';
import { useDonationAmounts } from '@givewp/donations/hooks';
import { amountFormatter } from '@givewp/src/Admin/utils';

/**
 * @since 4.6.0
 */
export default function DonationBreakdown({ donation }: { donation: Donation }) {
  const {isFeeRecoveryEnabled, currency: defaultCurrency, eventTicketsEnabled} = getDonationOptionsWindowData();
  const baseCurrencyFormatter = amountFormatter(defaultCurrency, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    roundingMode: 'trunc',
  });

  const baseCurrencyAmount = (donation?.amount?.value ?? 0) / Number(donation?.exchangeRate ?? 1);
  const {formatter, amount, intendedAmount, feeAmountRecovered, eventTicketsAmount} = useDonationAmounts(donation);

  const showFeeRecoveredRow = isFeeRecoveryEnabled;
  const showEventTicketRow = eventTicketsEnabled && Number(donation?.eventTicketsAmount?.value ?? 0) > 0;
  const showCurrencyBreakdownRow = donation?.amount?.currency !== defaultCurrency;

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
                  label={<EventLabel eventTickets={donation?.eventTickets} />}
                  value={formatter.format(eventTicketsAmount)}
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
 * @since 4.6.0
 */
type RowProps = {
  label?: React.ReactNode;
  value?: React.ReactNode;
  children?: React.ReactNode;
  className?: string;
};

/**
 * @since 4.6.0
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
