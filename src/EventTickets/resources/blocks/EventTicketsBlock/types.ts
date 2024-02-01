export type EventTicketsBlockItem = {
    id: number;
    name: string;
}

export interface EventTicketsBlockSettings {
    events: EventTicketsBlockItem[];
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
