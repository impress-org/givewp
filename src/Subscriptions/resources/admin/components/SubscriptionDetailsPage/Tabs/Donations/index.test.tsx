import {act} from 'react';
import {createRoot} from 'react-dom/client';
import SubscriptionDetailsPageDonationsTab from './index';

jest.mock('@givewp/subscriptions/utils', () => ({
    getSubscriptionOptionsWindowData: () => ({legacyApiRoot: 'https://example.test/api', apiNonce: 'nonce'}),
    useRefreshSubscriptionInBackground: () => jest.fn(),
}));
const mockListTablePage = jest.fn<null, [Record<string, any>]>(() => null);
jest.mock('@givewp/components', () => ({
    ListTablePage: jest.requireActual('react').forwardRef((props: Record<string, any>, _ref: unknown) => mockListTablePage(props)),
}));
jest.mock('@givewp/donations/components/DonationRowActions', () => ({donationRowActions: () => []}));
jest.mock('@givewp/components/ListTable/BlankSlate', () => () => null);
jest.mock('./AddRenewalModal', () => () => null);
jest.mock('@wordpress/data', () => ({useDispatch: () => ({})}));
jest.mock('@wordpress/core-data', () => ({store: {}}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

describe('subscription Donations tab', () => {
    // The list adds `/count` to the address, so a query string there would send the count to the rows route.
    it('passes the plain list address and sends the subscription as the scope', () => {
        window.history.replaceState(null, '', '/wp-admin/edit.php?id=8');
        const root = createRoot(document.createElement('div'));
        act(() => root.render(<SubscriptionDetailsPageDonationsTab />));

        const [props] = mockListTablePage.mock.calls[0];
        expect(props.apiSettings.apiRoot).toBe('https://example.test/api/donations');
        expect(props.scope).toEqual({subscriptionId: 8});

        act(() => root.unmount());
    });
});
