import {createContext, useContext} from 'react';
import {Ticket, TicketTypeFormContextType} from './types';

const defaultTicketData: Ticket = {
    id: undefined,
    title: '',
    description: '',
    price: undefined,
    capacity: undefined,
};

export const TicketTypeFormContext = createContext<TicketTypeFormContextType>({
    ticketData: defaultTicketData,
    setTicketData: () => {},
});

export const useTicketTypeForm = () => useContext(TicketTypeFormContext);
