import {__} from '@wordpress/i18n';
import {useEffect, useState} from 'react';
import EventTicketsList from '../../components/EventTicketsList';
import {EventTicketsListHOCProps, OnSelectTicketProps} from './types';

export default function EventTicketsListHOC({name, ticketTypes}: EventTicketsListHOCProps) {
    const [selectedTickets, setSelectedTickets] = useState([]);
    const {useWatch, useCurrencyFormatter, useDonationFormSettings, useDonationSummary, useFormContext} =
        window.givewp.form.hooks;
    const {currencySwitcherSettings} = useDonationFormSettings();
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const currencySettings = currencySwitcherSettings.find((setting) => setting.id === currency);
    const currencyRate = (currencySettings?.exchangeRate ?? Number('1.00')) || 1;
    const currencyFormatter = useCurrencyFormatter(currency, {
        minimumFractionDigits: currencySettings?.exchangeRateFractionDigits,
    });
    const donationSummary = useDonationSummary();

    useEffect(() => {
        let amount = 0;

        Object.keys(selectedTickets).forEach((ticketId) => {
            const ticket = ticketTypes.find((ticketType) => ticketType.id === Number(ticketId));
            const quantity = selectedTickets[ticketId]?.quantity ?? 0;

            if (quantity > 0) {
                const itemPrice = ticket.price * quantity * currencyRate;
                donationSummary.addItem({
                    id: `eventTickets-${ticketId}`,
                    label: `Ticket (${ticket.title}) x${quantity}`,
                    value: itemPrice > 0 ? currencyFormatter.format(itemPrice / 100) : __('Free', 'give'),
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
    }, [ticketTypes, selectedTickets, currency]);

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
                amount: selectedQuantity * ticketPrice * currencyRate,
            };

            return {...selectedTickets};
        });
    };

    return (
        <EventTicketsList
            ticketTypes={ticketTypes}
            currency={currency}
            currencyRate={currencyRate}
            selectedTickets={selectedTickets}
            handleSelect={onSelectTicket}
        />
    );
}
