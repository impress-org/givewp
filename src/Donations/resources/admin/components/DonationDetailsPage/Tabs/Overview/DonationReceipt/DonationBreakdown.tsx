import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import EventLabel from './EventsLabel';
import CurrencyBreakdownArrowIcon from './icon';
import styles from './styles.module.scss';
import { getDonationOptionsWindowData, useDonationEntityRecord } from '@givewp/donations/utils';
import { amountFormatter } from '@givewp/components/AdminDetailsPage/utils';

/**
 * @unreleased
 */
export default function DonationBreakdown() {
  const { isFeeRecoveryEnabled, currency: defaultCurrency } = getDonationOptionsWindowData();
  const { record: donation } = useDonationEntityRecord();

  const showFeeRecoveredRow = isFeeRecoveryEnabled;
  const showEventTicketRow = false; // Placeholder for future event ticket logic
  const showCurrencyBreakdownRow = donation?.amount?.currency !== defaultCurrency;

  const currencyFormatter = amountFormatter(defaultCurrency);
  const baseCurrencyFormatter = amountFormatter(donation?.amount?.currency);

  const donationAmount = donation?.amount?.value ?? 0;
  const feeRecoveredAmount = donation?.feeAmountRecovered?.value ?? 0;
  const baseAmount = donation?.amount?.value * (Number(donation?.exchangeRate) ?? 0);
  // TODO: Add event ticket amount when available
  // @ts-ignore
  const eventTicketAmount = donation?.eventTicketAmount?.value ?? 0;

  // Placeholder for event ticket details, to be replaced with real data
  const eventTicketDetailsArray: any[] = [];

  return (
      <div className={styles.rowContainer}>
          <Row
              className={styles.donationRow}
              label={__('Donation amount', 'give')}
              value={currencyFormatter.format(donationAmount)}
          />

          {showEventTicketRow && (
              <Row
                  className={styles.donationRow}
                  label={<EventLabel events={eventTicketDetailsArray} />}
                  value={currencyFormatter.format(eventTicketAmount)}
              />
          )}

          {showFeeRecoveredRow && (
              <Row
                  className={styles.donationRow}
                  label={__('Fee Recovered', 'give')}
                  value={currencyFormatter.format(feeRecoveredAmount)}
              />
          )}

          <Row
              className={styles.totalRow}
              label={<strong>{__('Total', 'give')}</strong>}
              value={<strong>{currencyFormatter.format(donationAmount)}</strong>}
          />

          {showCurrencyBreakdownRow && (
              <Row
                  className={styles.currencyRow}
                  label={__('Currency breakdown', 'give')}
                  value={
                      <>
                          {currencyFormatter.format(donationAmount)}
                          <CurrencyBreakdownArrowIcon />
                          {baseCurrencyFormatter.format(baseAmount)}
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
