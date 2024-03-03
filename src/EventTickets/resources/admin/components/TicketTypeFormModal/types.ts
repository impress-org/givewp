export type Ticket = {
    id?: number;
    title: string;
    description: string;
    price: number;
    capacity: number;
};

export type Inputs = {
    title: string;
    description: string;
    price: number;
    capacity: number;
};

export interface TicketModalProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    apiSettings: {
        apiRoot: string;
        apiNonce: string;
        event: {
            id: number;
        };
    };
    ticket?: Ticket;
}

export interface TicketTypeFormContextType {
    ticketData: Ticket | null;
    setTicketData: (data: Ticket | null) => void;
}
