import {readdirSync, readFileSync, statSync} from 'fs';
import {join, relative, resolve} from 'path';
import type {FilterConfig} from '../ListTablePage/types';
import {resolveScreenDefaults} from './screenDefaults';
import {LIST_TABLE_SCREEN_KEYS} from './types';
import type {ListTableScreen, ViewQueryContext} from './types';
import {viewQueryKeys} from './viewQuery';

// The create-campaign dialog loads the form builder's editor, which this test does not need.
jest.mock('@givewp/campaigns/admin/components/CreateCampaignModal', () => () => null);

const SHARED_DIR = resolve(__dirname, '..');

/**
 * The window data each standalone screen reads while its module loads.
 */
function stubWindowData() {
    const screenData = {apiRoot: 'https://example.test/wp-json', apiNonce: 'nonce', pluginUrl: '', adminUrl: '', forms: []};

    Object.assign(window, {
        GiveDonations: {...screenData, recurringDonationsEnabled: true},
        GiveDonors: screenData,
        GiveSubscriptions: screenData,
        GiveDonationForms: screenData,
        GiveCampaignsListTable: screenData,
        GiveCampaignOptions: {...screenData, admin: {}},
    });
}

type ScreenModule = {filters: FilterConfig[]; screen: ListTableScreen};

/**
 * The real filter settings and screen config of every standalone core screen.
 */
function standaloneScreens(): ScreenModule[] {
    stubWindowData();

    /* eslint-disable @typescript-eslint/no-var-requires */
    return [
        {
            filters: require('@givewp/src/Donations/resources/components/DonationsListTable').filters,
            screen: require('@givewp/src/Donations/resources/components/listTableScreen').default,
        },
        {
            filters: require('@givewp/src/Donors/resources/components/DonorsListTable').donorsFilters,
            screen: require('@givewp/src/Donors/resources/components/listTableScreen').default,
        },
        {
            filters: require('@givewp/src/Subscriptions/resources/components/SubscriptionsListTable').filters,
            screen: require('@givewp/src/Subscriptions/resources/components/listTableScreen').default,
        },
        {
            filters: require('@givewp/src/Campaigns/resources/admin/components/CampaignsListTable').filters,
            screen: require('@givewp/src/Campaigns/resources/admin/components/CampaignsListTable/listTableScreen').default,
        },
        {
            filters: require('@givewp/src/DonationForms/V2/resources/components/DonationFormsListTable')
                .donationFormsFilters,
            screen: require('@givewp/src/DonationForms/V2/resources/components/listTableScreen').default,
        },
    ];
    /* eslint-enable @typescript-eslint/no-var-requires */
}

function contextOf({filters, screen}: ScreenModule): ViewQueryContext {
    const columns = [{id: 'id', label: 'ID', sortable: true, visible: true}];

    return {
        filterSettings: filters,
        sortableFields: ['id'],
        defaults: resolveScreenDefaults(screen, columns, undefined, {filters: []}),
        siteTestMode: false,
    };
}

/**
 * Every non-test source file in the shared list table folder.
 */
function sharedSourceFiles(dir: string): string[] {
    return readdirSync(dir).flatMap((name) => {
        const path = join(dir, name);

        if (statSync(path).isDirectory()) {
            return sharedSourceFiles(path);
        }

        return /\.(ts|tsx|js|jsx)$/.test(name) && !/\.test\./.test(name) ? [path] : [];
    });
}

const withoutComments = (source: string) => source.replace(/\/\*[\s\S]*?\*\//g, '').replace(/(^|\s)\/\/.*$/gm, '$1');

describe('core list table screens', () => {
    it('give every standalone screen unique give- parameters', () => {
        const screens = standaloneScreens();

        expect(screens.map(({screen}) => screen.key)).toEqual([
            'donations',
            'donors',
            'subscriptions',
            'campaigns',
            'donation-forms',
        ]);

        screens.forEach((screenModule) => {
            const keys = viewQueryKeys(contextOf(screenModule));

            expect(keys.every((key) => key.startsWith('give-'))).toBe(true);
            expect(new Set(keys).size).toBe(keys.length);
        });
    });

    it('never name a screen key in shared list table code', () => {
        // `state/types.ts` declares the closed list of keys; it is the one place they belong.
        const declaration = join(SHARED_DIR, 'state', 'types.ts');
        const keyLiteral = new RegExp(`['"\`](${LIST_TABLE_SCREEN_KEYS.join('|')})['"\`]`);

        const offenders = sharedSourceFiles(SHARED_DIR)
            .filter((path) => path !== declaration)
            .filter((path) => keyLiteral.test(withoutComments(readFileSync(path, 'utf8'))))
            .map((path) => relative(SHARED_DIR, path));

        expect(offenders).toEqual([]);
    });
});
