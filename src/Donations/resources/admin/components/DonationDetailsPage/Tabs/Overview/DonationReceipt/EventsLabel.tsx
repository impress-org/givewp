import React from 'react';
import styles from './styles.module.scss'; // adjust path as needed
import type {EventTicket} from '@givewp/donations/admin/components/types';


type EventTicketWithQuantity = EventTicket & { quantity?: number };

/**
 * @unreleased
 */
export type EventLabelProps = {
    eventTickets?: EventTicketWithQuantity[];
};

const prepareEventTickets = (eventTickets: EventTicketWithQuantity[]) => {
    const tickets = eventTickets.reduce((acc: Record<number, EventTicketWithQuantity>, eventTicket) => {
        const ticketTypeId = eventTicket.ticketType.id;
        if (!acc[ticketTypeId]) {
            acc[ticketTypeId] = {
                ...eventTicket,
                quantity: 1,
            };
        } else {
            acc[ticketTypeId].quantity += 1;
        }
        return acc;
    }, {} as Record<number, EventTicketWithQuantity>);

    return Object.values(tickets) as EventTicketWithQuantity[];
};

/**
 * @unreleased
 */
export default function EventLabel({ eventTickets = [] }: EventLabelProps) {
    const tickets = prepareEventTickets(eventTickets);

  return (
    <div className={styles.eventLabel}>
      {tickets.map((eventTicket) => (
        <div key={eventTicket.id} className={styles.eventLabel}>
          {eventTicket.ticketType.title} (x{eventTicket.quantity})
          <a
            href={`/wp-admin/edit.php?post_type=give_forms&page=give-event-tickets&id=${eventTicket.event.id}`}
            className={styles.eventLink}
          >
            {eventTicket.event.title}
          </a>
        </div>
      ))}
    </div>
  );
}
