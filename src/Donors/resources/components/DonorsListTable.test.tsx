import {act} from 'react';
import {createRoot} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockPageProps = jest.fn();

jest.mock('@givewp/components', () => ({
    ListTableApi: class {},
    ListTablePage: (props: Record<string, unknown>) => {
        mockPageProps(props);
        return null;
    },
}));
jest.mock('@givewp/components/ListTable/ListTablePage/SwitchToLegacyViewButton', () => () => null);
jest.mock('@givewp/components/ListTable/BlankSlate', () => () => null);
jest.mock('@givewp/components/ListTable/ProductRecommendations', () => () => null);
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
            dissedRecommendations: [],
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
});
