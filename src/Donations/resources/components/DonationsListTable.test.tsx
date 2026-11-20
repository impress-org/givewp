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
jest.mock('@givewp/components/ListTable/api', () => ({__esModule: true, default: class {}}));
jest.mock('@givewp/components/ListTable/BlankSlate', () => () => null);
jest.mock('./DonationRowActions', () => ({donationRowActions: () => [], isDonationsTrashView: () => false}));

describe('DonationsListTable', () => {
    const original = window.GiveDonations;

    afterEach(() => {
        window.GiveDonations = original;
        mockPageProps.mockClear();
    });

    it('passes the recommendation options in order, with no productRecommendation', async () => {
        window.GiveDonations = {
            apiNonce: 'nonce-1',
            apiRoot: '',
            adminUrl: '',
            campaigns: [],
            table: {columns: []},
            paymentMode: false,
            manualDonations: true,
            recurringDonationsEnabled: false,
            pluginUrl: '',
            dismissedRecommendations: ['givewp_donations_fee_recovery_recommendation_dismissed'],
            addonsBulkActions: [],
            donationStatuses: {},
        };
        // Loaded after the window settings, as the module reads them when it loads.
        const {default: DonationsListTable} = await import('./DonationsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<DonationsListTable />));

        const props = mockPageProps.mock.calls[0][0];
        expect(props.productRecommendation).toBeUndefined();
        expect(props.recommendations.options.map((option: {enum: string}) => option.enum)).toEqual([
            'givewp_donations_recurring_recommendation_dismissed',
            'givewp_donations_fee_recovery_recommendation_dismissed',
            'givewp_donations_designated_funds_recommendation_dismissed',
        ]);
        expect(props.recommendations.dismissed).toEqual(['givewp_donations_fee_recovery_recommendation_dismissed']);
        expect(props.recommendations.apiNonce).toBe('nonce-1');

        await act(() => root.unmount());
    });

    it('offers "Switch to Legacy View" in the view menu, with its accessible name, and not in the header', async () => {
        window.GiveDonations = {
            ...original,
            apiNonce: '',
            apiRoot: '',
            adminUrl: '',
            campaigns: [],
            table: {columns: []},
            paymentMode: false,
            manualDonations: true,
            recurringDonationsEnabled: false,
            pluginUrl: '',
            dismissedRecommendations: [],
            addonsBulkActions: [],
            donationStatuses: {},
        };
        const {default: DonationsListTable} = await import('./DonationsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<DonationsListTable />));

        expect(mockPageProps.mock.calls[0][0].legacyView).toEqual({
            ariaLabel: 'Switch to the legacy donations table view',
        });
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await act(() => root.unmount());
    });
});
