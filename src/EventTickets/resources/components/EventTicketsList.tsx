import {useEffect, useState} from 'react';
import EventTicketsListItem from './EventTicketsListItem';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';

export default function EventTicketsList({tickets, ticketsLabel, soldOutMessage, config}) {
    const [selectedTickets, setSelectedTickets] = useState({});
    const {currency, useDonationSummary, setValue} = config;
    const formatter = useCurrencyFormatter(currency);
    const donationSummary = useDonationSummary && useDonationSummary();

    useEffect(() => {
        if (donationSummary && setValue) {
            let amount = 0;

            Object.keys(selectedTickets).forEach((ticketId) => {
                const ticket = tickets.find((ticket) => ticket.id === Number(ticketId));
                const quantity = selectedTickets[ticketId];

                if (quantity > 0) {
                    donationSummary.addItem({
                        id: `eventTickets-${ticketId}`,
                        label: `Ticket (${ticket.name})`,
                        value: formatter.format(ticket.price * quantity),
                    });
                    amount += ticket.price * quantity;
                } else {
                    donationSummary.removeItem(`eventTickets-${ticketId}`);
                }
            });

            if (amount > 0) {
                donationSummary.addToTotal('eventTickets', amount);
            } else {
                setValue('eventTickets', 0);
                donationSummary.removeFromTotal('eventTickets');
            }
        }
    }, [tickets, selectedTickets]);

    const selectedQuantity = (ticketId) => selectedTickets[ticketId] || 0;
    const handleSelect = (ticketId, ticketQuantity) => (selectedQuantity) => {
        if (selectedQuantity < 0) {
            selectedQuantity = 0;
        }

        if (selectedQuantity > ticketQuantity) {
            selectedQuantity = ticketQuantity;
        }

        setSelectedTickets({
            ...selectedTickets,
            [ticketId]: selectedQuantity,
        });
    };

    return (
        <div className={'givewp-event-tickets__tickets'}>
            <h4>{ticketsLabel}</h4>
            {tickets.map((ticket) => {
                return (
                    <EventTicketsListItem
                        ticket={ticket}
                        selectedQuantity={selectedQuantity(ticket.id)}
                        handleSelect={handleSelect(ticket.id, ticket.quantity)}
                        currency={currency}
                    />
                );
            })}
        </div>
    );
}
