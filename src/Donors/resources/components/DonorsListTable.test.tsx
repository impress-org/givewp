import {act} from 'react';
import {createRoot} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockPageProps = jest.fn();

jest.mock('@givewp/components', () => ({
    ListTableApi: class {},
    ListTablePage: (props: {children?: React.ReactNode}) => {
        mockPageProps(props);
        return <div>{props.children}</div>;
    },
}));
jest.mock('@givewp/components/ListTable/BlankSlate', () => () => null);
jest.mock('./DonorsRowActions', () => ({donorsRowActions: () => null}));

describe('DonorsListTable', () => {
    const original = window.GiveDonors;

    afterEach(() => {
        window.GiveDonors = original;
        mockPageProps.mockClear();
    });

    it('names one record "donor" and many "donors", as the result count says them', async () => {
        window.GiveDonors = {
            apiNonce: '',
            apiRoot: '',
            forms: [],
            table: {columns: []},
            adminUrl: '',
            pluginUrl: '',
            dismissedRecommendations: ['x'],
            recurringDonationsEnabled: false,
        };
        // Loaded after the window settings, as the module reads them when it loads.
        const {default: DonorsListTable} = await import('./DonorsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<DonorsListTable />));

        expect(mockPageProps).toHaveBeenCalledWith(
            expect.objectContaining({singleName: 'donor', pluralName: 'donors'})
        );

        await act(() => root.unmount());
    });

    it('offers only the fee recovery recommendation, as notice data', async () => {
        window.GiveDonors = {
            apiNonce: '',
            apiRoot: '',
            forms: [],
            table: {columns: []},
            adminUrl: '',
            pluginUrl: '',
            dismissedRecommendations: [],
            recurringDonationsEnabled: false,
        };
        const {default: DonorsListTable} = await import('./DonorsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<DonorsListTable />));

        const props = mockPageProps.mock.calls[0][0] as {
            productRecommendation?: unknown;
            recommendations: {options: Array<{enum: string}>; dismissed: string[]; apiNonce: string};
        };
        expect(props.productRecommendation).toBeUndefined();
        expect(props.recommendations.options.map((option) => option.enum)).toEqual([
            'givewp_donors_fee_recovery_recommendation_dismissed',
        ]);

        await act(() => root.unmount());
    });

    it('offers "Switch to Legacy View" in the view menu and not in the header', async () => {
        window.GiveDonors = {
            apiNonce: '',
            apiRoot: '',
            forms: [],
            table: {columns: []},
            adminUrl: '',
            pluginUrl: '',
            dismissedRecommendations: [],
            recurringDonationsEnabled: false,
        };
        const {default: DonorsListTable} = await import('./DonorsListTable');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<DonorsListTable />));

        expect(mockPageProps.mock.calls[0][0].legacyView).toEqual({});
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await act(() => root.unmount());
    });
});
