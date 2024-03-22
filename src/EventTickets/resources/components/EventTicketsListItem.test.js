import {fireEvent, render} from '@testing-library/react';
import EventTicketsListItem from './EventTicketsListItem';

describe('EventTicketsListItem', () => {
    test('renders the ticket title, description and price', () => {
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

    test('renders "Free" when the ticket price is 0', () => {
        const ticketType = {
            id: 1,
            price: 0,
        };

        const {getByText} = render(<EventTicketsListItem ticketType={ticketType} currency="USD" currencyRate={1} />);

        const priceElement = getByText('Free');
        expect(priceElement).toBeInTheDocument();
    });

    test('renders price with correct currency symbol and exchange rate', () => {
        const ticketType = {
            id: 1,
            price: 1000,
        };

        const {getByText} = render(<EventTicketsListItem ticketType={ticketType} currency="EUR" currencyRate={1.1} />);

        const priceElement = getByText('€11.00');
        expect(priceElement).toBeInTheDocument();
    });

    test('renders available tickets when ticketsAvailable is greater than 0', () => {
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

    test('renders the quantity input with the value from selectedTickets', () => {
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

    test('updates the available tickets when selectedTickets is greater than 0', () => {
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

    test('calls handleButtonClick with correct quantity on decrement button click', () => {
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

    test('calls handleButtonClick with correct quantity on increment button click', () => {
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

    test('renders "Sold Out" when ticketsAvailable is 0', () => {
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
