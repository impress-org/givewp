import {fireEvent, render} from '@testing-library/react';
import EventTicketsListItem from './EventTicketsListItem';

/**
 * @unreleased
 */
describe('EventTicketsListItem', () => {
    /**
     * @unreleased
     */
    test('renders ticket details including title, description, and price correctly', () => {
        const ticketType = {
            id: 1,
            title: 'General Admission',
            description: 'General Admission Description',
            price: 1000,
        };

        const {getByText} = render(<EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} />);

        const titleElement = getByText('General Admission');
        expect(titleElement).toBeInTheDocument();

        const descriptionElement = getByText('General Admission Description');
        expect(descriptionElement).toBeInTheDocument();

        const priceElement = getByText('$10.00');
        expect(priceElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('displays "Free" for tickets with a price of 0', () => {
        const ticketType = {
            id: 1,
            price: 0,
        };

        const {getByText} = render(<EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} />);

        const priceElement = getByText('Free');
        expect(priceElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('renders ticket price in a specified currency using the provided exchange rate', () => {
        const ticketType = {
            id: 1,
            price: 1000,
        };

        const {getByText} = render(<EventTicketsListItem ticketType={ticketType} currency="EUR" currencyRate={1.1} />);

        const priceElement = getByText('€11.00');
        expect(priceElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('displays the number of available tickets when greater than 0', () => {
        const ticketType = {
            id: 1,
            ticketsAvailable: 10,
        };

        const {getByText} = render(
            <EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} selectedTickets={0} />
        );

        const availableTicketsElement = getByText('10 remaining');
        expect(availableTicketsElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('shows the number of selected tickets in the quantity input field', () => {
        const ticketType = {
            id: 1,
            ticketsAvailable: 10,
        };

        const {getByDisplayValue} = render(
            <EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} selectedTickets={2} />
        );

        const selectedTicketsElement = getByDisplayValue('2');
        expect(selectedTicketsElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('updates the displayed number of remaining tickets based on the number of selected tickets', () => {
        const ticketType = {
            id: 1,
            ticketsAvailable: 10,
        };

        const {getByText} = render(
            <EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} selectedTickets={2} />
        );

        const availableTicketsElement = getByText('8 remaining');
        expect(availableTicketsElement).toBeInTheDocument();
    });

    /**
     * @unreleased
     */
    test('calls handleSelect with the decremented quantity when the decrement button is clicked', () => {
        const mockHandleSelect = jest.fn();
        const ticketType = {
            id: 1,
            ticketsAvailable: 10,
        };
        const selectedTickets = 2;

        const {getByLabelText} = render(
            <EventTicketsListItem
                ticketType={ticketType}
                currency="USD"
                currencyRate={1}
                selectedTickets={selectedTickets}
                handleSelect={mockHandleSelect}
            />
        );

        const incrementButton = getByLabelText('-');
        fireEvent.click(incrementButton);

        expect(mockHandleSelect).toHaveBeenCalledWith(selectedTickets - 1);
    });

    /**
     * @unreleased
     */
    test('calls handleSelect with the incremented quantity when the increment button is clicked', () => {
        const mockHandleSelect = jest.fn();
        const ticketType = {
            id: 1,
            ticketsAvailable: 10,
        };
        const selectedTickets = 2;

        const {getByLabelText} = render(
            <EventTicketsListItem
                ticketType={ticketType}
                currency="USD"
                currencyRate={1}
                selectedTickets={selectedTickets}
                handleSelect={mockHandleSelect}
            />
        );

        const incrementButton = getByLabelText('+');
        fireEvent.click(incrementButton);

        expect(mockHandleSelect).toHaveBeenCalledWith(selectedTickets + 1);
    });

    /**
     * @unreleased
     */
    test('displays "Sold Out" when there are no tickets available', () => {
        const ticketType = {
            id: 1,
            ticketsAvailable: 0,
        };

        const {getByText} = render(
            <EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} selectedTickets={0} />
        );

        const availableTicketsElement = getByText('Sold out');
        expect(availableTicketsElement).toBeInTheDocument();
    });
});
