import {act, type ReactElement} from 'react';
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

type PageProps = {
    scope?: {campaignId?: number};
    filters?: Array<{id: string}>;
    contentMode?: boolean;
    columnFilters: Array<{column: string; filter: (item: object) => ReactElement}>;
};

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

// "Default" means the default form of one campaign, so it says something only on that campaign's Forms tab.
describe('DonationFormsListTable default form tag', () => {
    const titleCell = async () => {
        const element = document.createElement('div');
        const cellRoot = createRoot(element);
        const title = lastProps().columnFilters.find(({column}) => column === 'title')!;
        await act(() => cellRoot.render(title.filter({title: 'Spring appeal', isDefaultCampaignForm: true})));
        const text = element.textContent;
        act(() => cellRoot.unmount());

        return text;
    };

    it('marks the default form on the campaign Forms tab', async () => {
        await act(() => root.render(<DonationFormsListTable campaignId={7} />));

        expect(await titleCell()).toContain('Default');
    });

    it('does not mark it on the full Forms list', async () => {
        await act(() => root.render(<DonationFormsListTable />));

        expect(await titleCell()).toBe('Spring appeal');
    });
});

// The tag is a quiet note after the name, like the other secondary text of the list, not a badge.
describe('DonationFormsListTable default form tag style', () => {
    const styles = require('fs').readFileSync(require('path').join(__dirname, 'DonationFormsListTable.module.scss'), 'utf8');
    const block = styles.slice(styles.indexOf('.defaultFormPill {'), styles.indexOf('}', styles.indexOf('.defaultFormPill {')));

    it('is gray text with no box', () => {
        expect(block).toContain('color: var(--givewp-list-table-text-secondary)');
        expect(block).toContain('font-weight: 400');
        expect(block).not.toMatch(/background|border|padding/);
    });
});
