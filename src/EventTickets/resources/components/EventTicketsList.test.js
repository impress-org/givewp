import {render} from '@testing-library/react';
import EventTicketsList from './EventTicketsList';

jest.mock('./EventTicketsListItem', () => (props) => (
    <div data-testid="event-tickets-list-item">Ticket #{props.ticketType.id}</div>
));

describe('EventTicketsList', () => {
    test('renders null if no ticket types are provided', () => {
        const {container} = render(<EventTicketsList ticketTypes={[]} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />);
        expect(container.firstChild).toBeNull();
    });

    test('renders the provided label', () => {
        const ticketTypes = [{id: 1}];

        const {getByText} = render(<EventTicketsList ticketTypes={ticketTypes} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />);

        const labelElement = getByText('Select tickets');
        expect(labelElement).toBeInTheDocument();
    });

    test('renders a list of ticket types', () => {
        const ticketTypes = [
            {id: 1},
            {id: 2},
        ];

        const {getAllByTestId} = render(
            <EventTicketsList ticketTypes={ticketTypes} ticketsLabel="Select tickets" currency="USD" currencyRate={1} />
        );

        const listItemElements = getAllByTestId('event-tickets-list-item');
        expect(listItemElements.length).toEqual(2);
    });
});
