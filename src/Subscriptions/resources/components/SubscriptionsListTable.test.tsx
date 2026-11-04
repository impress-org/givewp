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
jest.mock('@givewp/components/ListTable/BlankSlate', () => () => null);
jest.mock('./SubscriptionsRowActions', () => ({subscriptionsRowActions: []}));

describe('SubscriptionsListTable', () => {
    it('offers "Switch to Legacy View" in the view menu and not in the header', async () => {
        window.GiveSubscriptions = {
            apiNonce: '',
            apiRoot: '',
            table: {columns: []},
            forms: [],
            paymentMode: false,
            pluginUrl: '',
            subscriptionStatuses: {},
        };
        // Loaded after the window settings, as the module reads them when it loads.
        const {default: SubscriptionsListTable} = await import('./SubscriptionsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<SubscriptionsListTable />));

        expect(mockPageProps.mock.calls[0][0].legacyView).toEqual({});
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await act(() => root.unmount());
    });
});
