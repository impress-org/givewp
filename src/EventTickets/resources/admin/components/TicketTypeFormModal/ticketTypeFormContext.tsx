import { createContext, useContext } from '@wordpress/element';
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
