import {render, within} from '@testing-library/react';
import EventTicketsHeader from './EventTicketsHeader';

describe('EventTicketsHeader', () => {
    test('renders event details (day, month, title, and full formatted date) correctly', () => {
        const title = 'This is a test event title.';
        const startDateTime = new Date('2024-03-15 12:00:00');

        const {getByText, getByRole, container} = render(
            <EventTicketsHeader title={title} startDateTime={startDateTime} />
        );

        const dateWrapper = container.getElementsByTagName('div')[0];
        const dayElement = within(dateWrapper).getByText('15');
        const monthElement = within(dateWrapper).getByText('Mar');
        const titleElement = getByRole('heading', {name: title});
        const fullDateElement = getByText('Friday, March 15th, 12:00pm');
        expect(dayElement).toBeInTheDocument();
        expect(monthElement).toBeInTheDocument();
        expect(titleElement).toBeInTheDocument();
        expect(fullDateElement).toBeInTheDocument();
    });
});
