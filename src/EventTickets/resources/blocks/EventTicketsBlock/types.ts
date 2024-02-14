import {TicketType} from '../../components/types';

export type EventSettings = {
    id: number;
    title: string;
    date: Date;
    description: string;
    ticketTypes: TicketType[];
};

export interface EventTicketsBlockSettings {
    events: EventSettings[];
    createEventUrl: string;
    listEventsUrl: string;
    ticketsLabel: string;
    soldOutMessage: string;
}

declare global {
    interface Window {
        eventTicketsBlockSettings: EventTicketsBlockSettings;
    }
}
