import {TicketType} from '../../components/types';

export type EventSettings = {
    id: number;
    title: string;
    description: string;
    startDateTime: Date;
    endDateTime: Date;
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
