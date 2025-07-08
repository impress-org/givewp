import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import styles from './styles.module.scss';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { amountFormatter } from '@givewp/src/Admin/utils';

/**
 * @unreleased
 */
type DonationBreakdownProps = {
  amount?: string;
  intendedAmount?: string;
  feeAmountRecovered?: string;
  eventTicketAmount?: string | null;
  currency?: string;
  baseTotal?: string;
  exchangeRate?: string | number;
};

export default function DonationBreakdown({ amount, intendedAmount, feeAmountRecovered, eventTicketAmount, currency, baseTotal, exchangeRate }: DonationBreakdownProps){
  const {currency: defaultCurrency } = getDonationOptionsWindowData();

const donationAmount =  amountFormatter(currency).format(parseFloat(amount))
const donationIntendedAmount =  amountFormatter(currency).format(parseFloat(intendedAmount))
const donationFeeAmountRecovered =  amountFormatter(currency).format(parseFloat(feeAmountRecovered))
const baseTotalAmount =  amountFormatter(defaultCurrency).format(parseFloat(baseTotal))

const showFeeRecoveredRow = !!feeAmountRecovered && parseFloat(feeAmountRecovered) > 0;
const showEventTicketRow = !!eventTicketAmount && parseFloat(eventTicketAmount) > 0;

return (
    <div className={styles.rowContainer}>
      <Row className={styles.donationRow} label={__("Donation amount", 'give')} value={donationIntendedAmount} />
      {showEventTicketRow && (
        <Row className={styles.donationRow} label={<EventLabel />} value={eventTicketAmount} />
      )}
      {showFeeRecoveredRow && (
        <Row className={styles.donationRow} label={__('Fee Recoverd')} value={donationFeeAmountRecovered} />
      )}
      <Row label={<strong>{__("Total", 'give')}</strong>} value={<strong>{donationAmount}</strong>} />
      <a href="#" className={styles.close}>{__('Close', 'give')}</a>
      <Row className={styles.currencyRow} label={__("Exchange rate", 'give')} value={exchangeRate} />
      <Row className={styles.currencyRow} label={__("Base currency", 'give')} value={defaultCurrency} />
      <Row className={styles.currencyRow} label={__("Base total", 'give')} value={baseTotalAmount} />
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

function EventLabel({ eventName = __('Event name', 'give'), ticketType = __('Standard', 'give'), ticketCount = 1, onEventClick }: EventLabelProps) {
  return (
    <div className={styles.eventLabel}>
      <p>{`${ticketType} (x${ticketCount})`}</p>
      <a href="#" className={styles.eventLink} onClick={onEventClick}>{eventName}</a>
    </div>
  )
}

/**
 * @unreleased
 */
export type EventLabelProps = {
  eventName?: string;
  ticketType?: string;
  ticketCount?: number;
  onEventClick?: (event: React.MouseEvent<HTMLAnchorElement, MouseEvent>) => void;
};