import {Ticket} from '../../components/types';

export type EventTicketsListHOCProps = {
    name: string;
    tickets: Ticket[];
    ticketsLabel: string;
    soldOutMessage: string;
};

export interface OnSelectTicketProps {
    (ticketId: number, ticketQuantity: number, ticketPrice: number): (selectedQuantity: number) => void;
}
