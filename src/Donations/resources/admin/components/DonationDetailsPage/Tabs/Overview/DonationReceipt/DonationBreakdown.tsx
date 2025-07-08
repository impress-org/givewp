import React, {useState} from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
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

export default function DonationBreakdown(){
const [showCurrencyRows, setShowCurrencyRows] = useState<boolean>(false);

const handleShowCurrencyRows = () => {
  setShowCurrencyRows(!showCurrencyRows);
}

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
        <Row className={styles.donationRow} label={__('Fee Recoverd')} value={'1.00'} />
      )}
      <Row className={styles.totalRow} label={<strong>{__("Total", 'give')}</strong>} value={<strong>{'371.00'}</strong>} />
      <button
        className={styles.toggleCurrencyRows}
        onClick={handleShowCurrencyRows}
        type="button"
        aria-label={
          showCurrencyRows
            ? __('Close donation base currency details', 'give')
            : __('Open donation base currency details', 'give')
        }      >
        {showCurrencyRows ? __('Close', 'give') : __('Open', 'give')}
      </button>
      {showCurrencyRows && (
        <>
          <Row className={styles.currencyRow} label={__("Exchange rate", 'give')} value={'1.14'} />
          <Row className={styles.currencyRow} label={__("Base currency", 'give')} value={__("Euro (€)", 'give')} />
          <Row className={styles.currencyRow} label={__("Base total", 'give')} value={"€264.14"} />
        </>
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

function EventLabel({ events = [] }: EventLabelProps) {
  return (
    <div className={styles.eventLabel}>
      {events.map((event, eventIdx) => (
        <div key={event.eventId} className={styles.eventLabel}>
          {event.ticketTypes.map((ticket, ticketIdx) => (
            <p key={ticket.ticketTypeId}>
              {`${ticket.ticketName} (x${ticket.quantity})`}
            </p>
          ))}
          <a href={`/wp-admin/edit.php?post_type=give_forms&page=give-event-tickets&id=${event.eventId}`} className={styles.eventLink}>{event.eventName}</a>
        </div>
      ))}
    </div>
  );
}

/**
 * @unreleased
 */
export type EventLabelProps = {
  events?: Array<{
    eventId: number;
    eventName: string;
    ticketTypes: Array<{
      ticketTypeId: number;
      ticketName: string;
      quantity: number;
    }>;
  }>;
};

export type EventTicketDetails = {
  [key: string]: any;
};