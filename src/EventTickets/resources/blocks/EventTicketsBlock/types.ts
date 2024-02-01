export type EventTicketsBlockItem = {
    id: number;
    title: string;
};

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
