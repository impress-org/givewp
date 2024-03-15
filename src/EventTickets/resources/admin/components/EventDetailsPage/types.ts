export interface GiveEventTicketsDetails {
    apiNonce: string;
    apiRoot: string;
    event: Event;
    currencyCode: string;
    adminUrl: string;
    pluginUrl: string;
}

export type Event = {
    id: number;
    title: string;
    description: string;
    startDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    endDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    createdAt: string;
    updatedAt: string;
    ticketTypes: TicketType[];
    forms: DonationForm[];
    tickets: Ticket[];
};

export type TicketType = {
    id: number;
    eventId: number;
    title: string;
    description: string;
    price: number;
    capacity: number;
    salesCount: number;
};

export type DonationForm = {
    id: number;
    title: string;
};

export type Ticket = {
    id: number;
    ticketTypeId: number;
    attendee: {
        name: string;
        email: string;
    };
    createdAt: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
};
