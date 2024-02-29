import {__} from '@wordpress/i18n';
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
                const itemPrice = ticket.price * quantity;
                donationSummary.addItem({
                    id: `eventTickets-${ticketId}`,
                    label: `Ticket (${ticket.title})`,
                    value: itemPrice > 0 ? formatter.format(itemPrice / 100) : __('Free', 'give'),
                });
                amount += itemPrice;
            } else {
                donationSummary.removeItem(`eventTickets-${ticketId}`);
            }
        });

        if (amount > 0) {
            donationSummary.addToTotal('eventTickets', amount / 100);
        } else {
            donationSummary.removeFromTotal('eventTickets');
        }

        setValue(name, JSON.stringify(Object.values(selectedTickets)));
    }, [ticketTypes, selectedTickets]);

    const onSelectTicket: OnSelectTicketProps = (ticketId, ticketsAvailable, ticketPrice) => (selectedQuantity) => {
        if (selectedQuantity < 0) {
            selectedQuantity = 0;
        }

        if (selectedQuantity > ticketsAvailable) {
            selectedQuantity = ticketsAvailable;
        }

        setSelectedTickets((selectedTickets) => {
            selectedTickets[ticketId] = {
                ticketId,
                quantity: selectedQuantity,
                amount: selectedQuantity * ticketPrice,
            };

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
