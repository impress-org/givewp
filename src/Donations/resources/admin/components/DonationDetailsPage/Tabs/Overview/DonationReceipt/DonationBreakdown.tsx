import React, {useState} from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import EventLabel from './EventsLabel';
import CurrencyBreakdownArrowIcon from './icon';
import styles from './styles.module.scss';

const eventTicketDetails = [
  {
      "eventId": 3,
      "eventName": "Food Festival",
      "ticketTypes": [
          {
              "ticketTypeId": 4,
              "ticketName": "Basic",
              "quantity": 2
          },
          {
              "ticketTypeId": 5,
              "ticketName": "Pro",
              "quantity": 1
          }
      ]
  }
]

/**
 * @unreleased
 */
export default function DonationBreakdown(){

const showFeeRecoveredRow = true;
const showEventTicketRow = true;

const eventTicketDetailsArray = eventTicketDetails
  ? Array.isArray(eventTicketDetails)
    ? eventTicketDetails
    : [eventTicketDetails]
  : [];

return (
    <div className={styles.rowContainer}>
      <Row className={styles.donationRow} label={__("Donation amount", 'give')} value={"$270.00"} />
      {showEventTicketRow && (
        <Row className={styles.donationRow} label={<EventLabel events={eventTicketDetailsArray} />} value={"$100.00"} />
      )}
      {showFeeRecoveredRow && (
        <Row className={styles.donationRow} label={__('Fee Recoverd')} value={'$1.00'} />
      )}
      <Row className={styles.totalRow} label={<strong>{__("Total", 'give')}</strong>} value={<strong>{'$371.00'}</strong>} />
      <Row className={styles.currencyRow} label={__("Currency breakdown", 'give')} value={<>€344.21 <CurrencyBreakdownArrowIcon/> $371.00</>} />
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
