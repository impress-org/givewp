import {useEffect, useState} from 'react';
import EventTicketsList from '../../components/EventTicketsList';
import {EventTicketsListHOCProps, OnSelectTicketProps} from './types';

export default function EventTicketsListHOC({name, ticketTypes, ticketsLabel}: EventTicketsListHOCProps) {
    const [selectedTickets, setSelectedTickets] = useState([]);
    const {useWatch, useCurrencyFormatter, useDonationSummary, useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const donationSummary = useDonationSummary();

    useEffect(() => {
        let amount = 0;

        Object.keys(selectedTickets).forEach((ticketId) => {
            const ticket = ticketTypes.find((ticketType) => ticketType.id === Number(ticketId));
            const quantity = selectedTickets[ticketId]?.quantity ?? 0;

            if (quantity > 0) {
                donationSummary.addItem({
                    id: `eventTickets-${ticketId}`,
                    label: `Ticket (${ticket.label})`,
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
    }, [ticketTypes, selectedTickets]);

    const onSelectTicket: OnSelectTicketProps = (ticketId, totalTickets, ticketPrice) => (selectedQuantity) => {
        if (selectedQuantity < 0) {
            selectedQuantity = 0;
        }

        if (selectedQuantity > totalTickets) {
            selectedQuantity = totalTickets;
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
            ticketTypes={ticketTypes}
            ticketsLabel={ticketsLabel}
            currency={currency}
            selectedTickets={selectedTickets}
            handleSelect={onSelectTicket}
        />
    );
}
