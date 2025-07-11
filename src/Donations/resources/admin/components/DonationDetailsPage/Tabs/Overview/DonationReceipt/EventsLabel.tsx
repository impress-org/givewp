import React from 'react';
import styles from './styles.module.scss'; // adjust path as needed

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

/**
 * @unreleased
 */
export default function EventLabel({ events = [] }: EventLabelProps) {
  return (
    <div className={styles.eventLabel}>
      {events.map((event) => (
        <div key={event.eventId} className={styles.eventLabel}>
          {event.ticketTypes.map((ticket) => (
            <p key={ticket.ticketTypeId}>
              {`${ticket.ticketName} (x${ticket.quantity})`}
            </p>
          ))}
          <a
            href={`/wp-admin/edit.php?post_type=give_forms&page=give-event-tickets&id=${event.eventId}`}
            className={styles.eventLink}
          >
            {event.eventName}
          </a>
        </div>
      ))}
    </div>
  );
}
