export type TicketType = {
    id?: number;
    eventId?: number;
    title: string;
    description: string;
    price: number;
    capacity: number;
    salesCount: number;
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
    };
    eventId: number;
}

export interface TicketTypeFormContextType {
    ticketData: TicketType | null;
    setTicketData: (data: TicketType | null) => void;
}
