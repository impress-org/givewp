export type Event = {
    id: number;
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
