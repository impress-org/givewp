import {createContext, useContext} from 'react';
import {TicketTypeFormContextType} from './types';

const defaultTicketData = {
    id: undefined,
    eventId: undefined,
    title: '',
    description: '',
    price: undefined,
    capacity: undefined,
    salesCount: 0,
};

export const TicketTypeFormContext = createContext<TicketTypeFormContextType>({
    ticketData: defaultTicketData,
    setTicketData: () => {},
});

export const useTicketTypeForm = () => useContext(TicketTypeFormContext);
