import {render} from '@testing-library/react';
import EventTicketsList from './EventTicketsList';

jest.mock('./EventTicketsListItem', () => (props) => (
    <div data-testid="event-tickets-list-item">Ticket #{props.ticketType.id}</div>
));

describe('EventTicketsList', () => {
    test('renders null when ticketTypes is an empty array', () => {
        const {container} = render(
            <EventTicketsList ticketTypes={[]} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />
        );
        expect(container.firstChild).toBeNull();
    });

    test('displays the provided ticketsLabel', () => {
        const ticketTypes = [{id: 1}];

        const {getByText} = render(
            <EventTicketsList ticketTypes={ticketTypes} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />
        );

        const labelElement = getByText('Select tickets');
        expect(labelElement).toBeInTheDocument();
    });

    test('renders an EventTicketsListItem for each ticket type provided', () => {
        const ticketTypes = [{id: 1}, {id: 2}];

        const {getAllByTestId} = render(
            <EventTicketsList ticketTypes={ticketTypes} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />
        );

        const listItemElements = getAllByTestId('event-tickets-list-item');
        expect(listItemElements.length).toEqual(2);
    });
});
