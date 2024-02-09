import {Ticket} from '../../components/types';

export type EventSettings = {
    id: number;
    title: string;
    date: string; // ISO 8601 format date string
    description: string;
    tickets: Ticket[];
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
