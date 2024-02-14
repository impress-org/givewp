import {OnSelectTicketProps} from '../templates/EventTickets/types';

export type Event = {
    id: number;
    name: string;
    title: string;
    date: Date;
    description: string;
    ticketTypes: TicketType[];
    ticketsLabel: string;
    soldOutMessage: string;
};

export type TicketType = {
    id: number;
    label: string;
    description: string;
    max_available: number;
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
    selectedTickets?: SelectedTicket[];
    handleSelect?: OnSelectTicketProps;
};

