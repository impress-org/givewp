import {OnSelectTicketProps} from '../templates/EventTickets/types';

export type Event = {
    id: number;
    name: string;
    title: string;
    date: string; // ISO 8601 format date string
    description: string;
    tickets: Ticket[];
    ticketsLabel: string;
    soldOutMessage: string;
};

export type Ticket = {
    id: number;
    name: string;
    price: number;
    quantity: number;
    description: string;
};

export type SelectedTicket = {
    id: number;
    quantity: number;
    price: number;
};

export type EventTicketsListProps = {
    tickets: Ticket[];
    ticketsLabel: string;
    soldOutMessage: string;
    currency: string;
    selectedTickets?: SelectedTicket[];
    handleSelect?: OnSelectTicketProps;
};

