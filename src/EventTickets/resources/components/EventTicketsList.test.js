import {render} from '@testing-library/react';
import EventTicketsList from './EventTicketsList';

/**
 * @unreleased
 */
jest.mock('./EventTicketsListItem', () => (props) => (
    <div data-testid="event-tickets-list-item">Ticket #{props.ticketType.id}</div>
));

/**
 * @unreleased
 */
describe('EventTicketsList', () => {
    /**
     * @unreleased
     */
    test('renders null when ticketTypes is an empty array', () => {
        const {container} = render(<EventTicketsList ticketTypes={[]} currency="USD" currencyRate={1} />);
        expect(container.firstChild).toBeNull();
    });

    /**
     * @unreleased
     */
    test('renders an EventTicketsListItem for each ticket type provided', () => {
        const ticketTypes = [{id: 1}, {id: 2}];

        const {getAllByTestId} = render(<EventTicketsList ticketTypes={ticketTypes} currency="USD" currencyRate={1} />);

        const listItemElements = getAllByTestId('event-tickets-list-item');
        expect(listItemElements.length).toEqual(2);
    });
});
