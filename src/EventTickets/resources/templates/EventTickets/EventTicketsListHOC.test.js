import {fireEvent, render} from '@testing-library/react';
import {act} from 'react-dom/test-utils';
import EventTicketsListHOC from './EventTicketsListHOC';

/**
 * @unreleased
 */
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
                    <button
                        data-testid="ticket-invalid-button"
                        onClick={(e) => {
                            e.preventDefault();
                            props.handleSelect(Infinity, 10, 1000)(quantity + 1);
                        }}
                    />
                </div>
            );
        })}
    </div>
));

/**
 * @unreleased
 */
describe('EventTicketsListHOC', () => {
    /**
     * @unreleased
     */
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

    /**
     * @unreleased
     */
    test('renders the EventTicketsList component with default props', () => {
        const {getByTestId, container} = render(<EventTicketsListHOC ticketTypes={[]} name="eventTickets" />);

        const eventTicketsList = getByTestId('event-tickets-list');
        expect(eventTicketsList).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('updates the form field value when a ticket is selected', () => {
        const {useFormContext} = window.givewp.form.hooks;
        const {setValue: mockSetValue} = useFormContext();

        const {getByTestId} = render(
            <EventTicketsListHOC name="eventTickets" ticketTypes={[{id: 1, price: 1000, ticketsAvailable: 10}]} />
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

    /**
     * @unreleased
     */
    test('ensures selected ticket quantity is never negative', () => {
        const {useFormContext} = window.givewp.form.hooks;
        const {setValue: mockSetValue} = useFormContext();

        const {getByTestId} = render(
            <EventTicketsListHOC name="eventTickets" ticketTypes={[{id: 1, price: 1000, ticketsAvailable: 10}]} />
        );

        expect(mockSetValue).toHaveBeenCalledTimes(1);
        expect(mockSetValue).toHaveBeenCalledWith('eventTickets', JSON.stringify([]));

        act(() => {
            const buttonElement = getByTestId('ticket-decrement-button');
            fireEvent.click(buttonElement);
        });

        expect(mockSetValue).toHaveBeenCalledTimes(2);
        expect(mockSetValue).toHaveBeenCalledWith(
            'eventTickets',
            JSON.stringify([{ticketId: 1, quantity: 0, amount: 0}])
        );
    });

    /**
     * @unreleased
     */
    test('ensures selected ticket quantity does not exceed available tickets', () => {
        const {useFormContext} = window.givewp.form.hooks;
        const {setValue: mockSetValue} = useFormContext();

        const {getByTestId} = render(
            <EventTicketsListHOC name="eventTickets" ticketTypes={[{id: 1, price: 1000, ticketsAvailable: 1}]} />
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

        act(() => {
            const buttonElement = getByTestId('ticket-increment-button');
            fireEvent.click(buttonElement);
        });

        expect(mockSetValue).toHaveBeenCalledTimes(3);
        expect(mockSetValue).toHaveBeenCalledWith(
            'eventTickets',
            JSON.stringify([{ticketId: 1, quantity: 1, amount: 1000}])
        );
    });

    /**
     * @unreleased
     */
    test('adds ticket to donation summary when quantity increases from 0', () => {
        const {useDonationSummary} = window.givewp.form.hooks;
        const {addItem: mockAddItem, addToTotal: mockAddToTotal} = useDonationSummary();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, title: 'General Admission', price: 1000, ticketsAvailable: 10}]}
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

    /**
     * @unreleased
     */
    test('adds ticket to donation summary with "Free" price when quantity increases from 0 and ticket price is 0', () => {
        const {useDonationSummary} = window.givewp.form.hooks;
        const {addItem: mockAddItem, addToTotal: mockAddToTotal} = useDonationSummary();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, title: 'General Admission', price: 0, ticketsAvailable: 10}]}
            />
        );

        act(() => {
            const buttonElement = getByTestId('ticket-increment-button');
            fireEvent.click(buttonElement);
        });

        expect(mockAddItem).toHaveBeenCalledWith({
            id: 'eventTickets-1',
            label: 'Ticket (General Admission) x1',
            value: 'Free',
        });
    });

    /**
     * @unreleased
     */
    test('ensures an invalid ticket type is not added to the form field value', () => {
        const {useFormContext} = window.givewp.form.hooks;
        const {setValue: mockSetValue} = useFormContext();

        const {getByTestId} = render(
            <EventTicketsListHOC name="eventTickets" ticketTypes={[{id: 1, price: 1000, ticketsAvailable: 10}]} />
        );

        expect(mockSetValue).toHaveBeenCalledTimes(1);
        expect(mockSetValue).toHaveBeenCalledWith('eventTickets', JSON.stringify([]));

        act(() => {
            const buttonElement = getByTestId('ticket-invalid-button');
            fireEvent.click(buttonElement);
        });

        expect(mockSetValue).toHaveBeenCalledTimes(2);
        expect(mockSetValue).toHaveBeenCalledWith('eventTickets', JSON.stringify([]));
    });

    /**
     * @unreleased
     */
    test('removes ticket from donation summary when quantity returns to 0', () => {
        const {useDonationSummary} = window.givewp.form.hooks;
        const {removeItem: mockRemoveItem, removeFromTotal: mockRemoveFromTotal} = useDonationSummary();

        const {getByTestId} = render(
            <EventTicketsListHOC
                name="eventTickets"
                ticketTypes={[{id: 1, title: 'General Admission', price: 1000, ticketsAvailable: 10}]}
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
