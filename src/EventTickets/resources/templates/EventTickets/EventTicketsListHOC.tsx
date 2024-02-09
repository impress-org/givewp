import {useEffect, useState} from 'react';
import EventTicketsList from '../../components/EventTicketsList';
import {EventTicketsListHOCProps, OnSelectTicketProps} from './types';

export default function EventTicketsListHOC({name, tickets, ticketsLabel, soldOutMessage}: EventTicketsListHOCProps) {
    const [selectedTickets, setSelectedTickets] = useState([]);
    const {useWatch, useCurrencyFormatter, useDonationSummary, useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const donationSummary = useDonationSummary();

    useEffect(() => {
        let amount = 0;

        Object.keys(selectedTickets).forEach((ticketId) => {
            const ticket = tickets.find((ticket) => ticket.id === Number(ticketId));
            const quantity = selectedTickets[ticketId]?.quantity ?? 0;

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
            donationSummary.removeFromTotal('eventTickets');
        }

        setValue(name, JSON.stringify(Object.values(selectedTickets)));
    }, [tickets, selectedTickets]);

    const onSelectTicket: OnSelectTicketProps = (ticketId, ticketQuantity, ticketPrice) => (selectedQuantity) => {
        if (selectedQuantity < 0) {
            selectedQuantity = 0;
        }

        if (selectedQuantity > ticketQuantity) {
            selectedQuantity = ticketQuantity;
        }

        setSelectedTickets((selectedTickets) => {
            if (selectedQuantity === 0) {
                delete selectedTickets[ticketId];
            } else {
                selectedTickets[ticketId] = {
                    ticketId,
                    quantity: selectedQuantity,
                    amount: selectedQuantity * ticketPrice,
                };
            }

            return {...selectedTickets};
        });
    };

    return (
        <EventTicketsList
            tickets={tickets}
            ticketsLabel={ticketsLabel}
            soldOutMessage={soldOutMessage}
            currency={currency}
            selectedTickets={selectedTickets}
            handleSelect={onSelectTicket}
        />
    );
}
