import {TicketType} from '../../components/types';

export type EventTicketsListHOCProps = {
    name: string;
    ticketTypes: TicketType[];
    ticketsLabel: string;
};

export interface OnSelectTicketProps {
    (ticketId: number, totalTickets: number, ticketPrice: number): (selectedQuantity: number) => void;
}
