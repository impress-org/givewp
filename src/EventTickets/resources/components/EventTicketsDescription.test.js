import {render} from '@testing-library/react';
import EventTicketsDescription from './EventTicketsDescription';

/**
 * @unreleased
 */
describe('EventTicketsDescription', () => {
    /**
     * @unreleased
     */
    test('renders the provided description', () => {
        const testDescription = 'This is a test event description.';

        const {getByText} = render(<EventTicketsDescription description={testDescription} />);

        const descriptionElement = getByText(testDescription);
        expect(descriptionElement).toBeInTheDocument();
    });
});
