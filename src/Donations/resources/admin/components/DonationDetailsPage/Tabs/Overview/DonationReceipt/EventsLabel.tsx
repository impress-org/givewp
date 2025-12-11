import React from 'react';
import styles from './styles.module.scss'; // adjust path as needed
import type {EventTicket} from '@givewp/donations/admin/components/types';


/**
 * @since 4.6.0
 */
type EventTicketWithQuantity = EventTicket & { quantity?: number };

/**
 * @since 4.6.0
 */
export type EventLabelProps = {
    eventTickets?: EventTicketWithQuantity[];
};

/**
 * This function is used to prepare the event tickets for the event label.
 * It is used to group the event tickets by ticket type and add the quantity to the event ticket.
 * @since 4.6.0
 */
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
 * @since 4.6.0
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
