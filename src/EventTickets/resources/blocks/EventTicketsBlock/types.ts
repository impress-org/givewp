export type Event = {
    id: number;
    title: string;
    date: string; // ISO 8601 format date string
    description: string;
    tickets: Ticket[];
};

export type Ticket = {
    id: number;
    name: string;
    price: number;
    quantity: number;
    description: string;
};

export interface EventTicketsBlockSettings {
    events: Event[];
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
