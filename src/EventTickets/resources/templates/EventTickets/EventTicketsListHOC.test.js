import {fireEvent, render} from '@testing-library/react';
import {act} from 'react-dom/test-utils';
import EventTicketsListHOC from './EventTicketsListHOC';

jest.mock('../../components/EventTicketsList', () => (props) => (
    <div data-testid="event-tickets-list">
        {props.ticketTypes.map((ticketType) => {
            const handleSelect = props.handleSelect(ticketType.id, ticketType.ticketsAvailable, ticketType.price);
            const quantity = props.selectedTickets[ticketType.id]?.quantity ?? 0;

            return (
                <div data-testid="event-tickets-list-item" key={ticketType.id}>
                    <button
                        data-testid="ticket-decrement-button"
                        onClick={(e) => {
                            e.preventDefault();
                            handleSelect(quantity - 1);
                        }}
                    />
                    <button
                        data-testid="ticket-increment-button"
                        onClick={(e) => {
                            e.preventDefault();
                            handleSelect(quantity + 1);
                        }}
                    />
                </div>
            );
        })}
    </div>
));

describe('EventTicketsListHOC', () => {
    beforeEach(() => {
        window.givewp = {
            form: {
                hooks: {
                    useWatch: jest.fn().mockReturnValue('USD'),
                    useCurrencyFormatter: jest.fn().mockReturnValue({format: (value) => `$${value.toFixed(2)}`}),
                    useDonationFormSettings: jest.fn().mockReturnValue({
                        currencySwitcherSettings: [{id: 'USD'}],
                    }),
                    useDonationSummary: jest.fn().mockReturnValue({
                        addItem: jest.fn(),
                        removeItem: jest.fn(),
                        addToTotal: jest.fn(),
                        removeFromTotal: jest.fn(),
                    }),
                    useFormContext: jest.fn().mockReturnValue({setValue: jest.fn()}),
                },
            },
        };
    });

    test('renders the EventTicketsList component with default props', () => {
        const {getByTestId, container} = render(
            <EventTicketsListHOC ticketTypes={[]} name="eventTickets" ticketsLabel="Select tickets" />
        );

        const eventTicketsList = getByTestId('event-tickets-list');
        expect(eventTicketsList).toBeInTheDocument();
    });

    test('updates the form field value when a ticket is selected', () => {
        const {useFormContext} = window.givewp.form.hooks;
        const {setValue: mockSetValue} = useFormContext();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, price: 1000, ticketsAvailable: 10}]}
                ticketsLabel="Tickets"
            />
        );

        expect(mockSetValue).toHaveBeenCalledTimes(1);
        expect(mockSetValue).toHaveBeenCalledWith('eventTickets', JSON.stringify([]));

        act(() => {
            const buttonElement = getByTestId('ticket-increment-button');
            fireEvent.click(buttonElement);
        });

        expect(mockSetValue).toHaveBeenCalledTimes(2);
        expect(mockSetValue).toHaveBeenCalledWith(
            'eventTickets',
            JSON.stringify([{ticketId: 1, quantity: 1, amount: 1000}])
        );
    });

    test('adds ticket to donation summary when quantity increases from 0', () => {
        const {useDonationSummary} = window.givewp.form.hooks;
        const {addItem: mockAddItem, addToTotal: mockAddToTotal} = useDonationSummary();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, title: 'General Admission', price: 1000, ticketsAvailable: 10}]}
                ticketsLabel="Tickets"
            />
        );

        act(() => {
            const buttonElement = getByTestId('ticket-increment-button');
            fireEvent.click(buttonElement);
        });

        expect(mockAddItem).toHaveBeenCalledWith({
            id: 'eventTickets-1',
            label: 'Ticket (General Admission) x1',
            value: '$10.00',
        });
        expect(mockAddToTotal).toHaveBeenCalledWith('eventTickets', 10);
    });

    test('removes ticket from donation summary when quantity returns to 0', () => {
        const {useDonationSummary} = window.givewp.form.hooks;
        const {removeItem: mockRemoveItem, removeFromTotal: mockRemoveFromTotal} = useDonationSummary();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, title: 'General Admission', price: 1000, ticketsAvailable: 10}]}
                ticketsLabel="Tickets"
            />
        );

        act(() => {
            const incrementButtonElement = getByTestId('ticket-increment-button');
            fireEvent.click(incrementButtonElement);

            const decrementButtonElement = getByTestId('ticket-decrement-button');
            fireEvent.click(decrementButtonElement);
        });

        expect(mockRemoveItem).toHaveBeenCalledWith('eventTickets-1');
        expect(mockRemoveFromTotal).toHaveBeenCalledWith('eventTickets');
    });
});
