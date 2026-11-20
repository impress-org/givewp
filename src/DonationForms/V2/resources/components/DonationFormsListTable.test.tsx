import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

(window as unknown as {GiveDonationForms: object}).GiveDonationForms = {
    apiNonce: 'nonce',
    apiRoot: '/give-api/v2/admin/forms',
    table: {columns: []},
    showDefaultFormTooltip: false,
    isOptionBasedFormEditorEnabled: false,
    newFormUrl: '/new-form',
};

type PageProps = {scope?: {campaignId?: number}; filters?: Array<{id: string}>; contentMode?: boolean};

const pageProps: PageProps[] = [];

jest.mock('@givewp/components', () => ({
    ListTableApi: class {},
    ListTablePage: (props: PageProps) => {
        pageProps.push(props);

        return null;
    },
}));

// Loaded after the window globals above exist: the module reads them when it is imported.
// eslint-disable-next-line @typescript-eslint/no-var-requires
const DonationFormsListTable = require('./DonationFormsListTable').default;

let root: Root;

beforeEach(() => {
    pageProps.length = 0;
    root = createRoot(document.createElement('div'));
});

afterEach(() => act(() => root.unmount()));

const lastProps = () => pageProps[pageProps.length - 1];

describe('DonationFormsListTable campaign scope', () => {
    it('fixes the request to the campaign and drops the Campaign filter', async () => {
        await act(() => root.render(<DonationFormsListTable campaignId={7} />));

        expect(lastProps().scope).toEqual({campaignId: 7});
        expect(lastProps().contentMode).toBe(true);
        expect(lastProps().filters?.map((filter) => filter.id)).not.toContain('campaign');
    });

    it('has no scope and offers the Campaign filter on the standalone list', async () => {
        await act(() => root.render(<DonationFormsListTable />));

        expect(lastProps().scope).toBeUndefined();
        expect(lastProps().contentMode).toBe(false);
        expect(lastProps().filters?.map((filter) => filter.id)).toContain('campaign');
    });
});
