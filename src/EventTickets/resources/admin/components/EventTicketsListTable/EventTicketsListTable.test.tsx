import {act} from 'react';
import {createRoot} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockPageProps = jest.fn();

jest.mock('@givewp/components', () => ({
    ListTablePage: (props: {children?: React.ReactNode}) => {
        mockPageProps(props);
        return <div>{props.children}</div>;
    },
}));
jest.mock('./EventTicketsRowActions', () => ({eventTicketsRowActions: []}));
jest.mock('../CreateEventModal', () => () => null);
jest.mock('../../feedback', () => () => null);

describe('EventTicketsListTable', () => {
    it('offers no "Switch to Legacy View": Event Tickets has no legacy screen', async () => {
        window.GiveEventTickets = {apiNonce: '', apiRoot: '', table: {columns: []}, adminUrl: '', pluginUrl: ''};
        const {default: EventTicketsListTable} = await import('./index');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<EventTicketsListTable />));

        expect(mockPageProps.mock.calls[0][0].legacyView).toBeUndefined();
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await act(() => root.unmount());
    });
});
