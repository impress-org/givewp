import {OnSelectTicketProps} from '../templates/EventTickets/types';

export type Event = {
    id: number;
    name: string;
    title: string;
    startDateTime: Date;
    description: string;
    ticketTypes: TicketType[];
    ticketsLabel: string;
    soldOutMessage: string;
};

export type TicketType = {
    id: number;
    title: string;
    description: string;
    capacity: number;
    ticketsAvailable: number;
    price: number;
};

export type SelectedTicket = {
    id: number;
    quantity: number;
    price: number;
};

export type EventTicketsListProps = {
    ticketTypes: TicketType[];
    ticketsLabel: string;
    currency: string;
    currencyRate: number;
    selectedTickets?: SelectedTicket[];
    handleSelect?: OnSelectTicketProps;
};

