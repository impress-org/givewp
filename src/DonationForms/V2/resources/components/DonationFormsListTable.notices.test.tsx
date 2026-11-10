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
jest.mock('./DonationFormsRowActions', () => ({useDonationFormsRowActions: () => []}));
jest.mock('./AddCampaignFormModal', () => () => null);
jest.mock('./UpgradedFormTooltip', () => () => null);
jest.mock('./Onboarding', () => ({
    __esModule: true,
    default: () => null,
    OnboardingContext: require('react').createContext([]),
}));
jest.mock('@givewp/components/AdminUI/Icons', () => ({CubeIcon: () => null}));
jest.mock('@wordpress/api-fetch', () => ({__esModule: true, default: jest.fn(() => Promise.resolve())}));

const settings = {
    apiNonce: '',
    bannerActionUrl: '',
    tooltipActionUrl: '',
    migrationApiRoot: '',
    defaultFormActionUrl: '/default-form',
    apiRoot: '',
    authors: [],
    table: {columns: []},
    pluginUrl: '',
    showUpgradedTooltip: false,
    isMigrated: false,
    migratedFormUrl: '',
    supportedAddons: [],
    supportedGateways: [],
    isOptionBasedFormEditorEnabled: false,
    locale: 'en-US',
    showDefaultFormTooltip: true,
    campaignUrl: '',
    newFormUrl: '',
    adminUrl: '',
};

describe('DonationFormsListTable', () => {
    afterEach(() => mockPageProps.mockClear());

    let container: HTMLDivElement;

    const render = async (campaignId?: number, overrides: Partial<typeof settings> = {}) => {
        window.GiveDonationForms = {...settings, ...overrides};
        const {default: DonationFormsListTable} = await import('./DonationFormsListTable');
        container = document.createElement('div');
        const root = createRoot(container);
        await act(() => root.render(<DonationFormsListTable campaignId={campaignId} />));
        return () => act(() => root.unmount());
    };

    it('passes the default form notice on a campaign page, with its text and a dismiss handler', async () => {
        const unmount = await render(5);

        const {notices} = mockPageProps.mock.calls[0][0];
        expect(notices).toHaveLength(1);
        expect(notices[0]).toMatchObject({
            id: 'default-form',
            kind: 'onboarding',
            title: 'Default campaign form',
            message:
                'The default form will always appear at the top of this list. Your campaign page and blocks will collect donations through this form by default. You can change it at any time.',
        });
        expect(typeof notices[0].onDismiss).toBe('function');

        await unmount();
    });

    it('passes no notice on the standalone Forms screen', async () => {
        const unmount = await render();

        expect(mockPageProps.mock.calls[0][0].notices ?? []).toEqual([]);

        await unmount();
    });

    it('offers "Switch to Legacy View" in the view menu when the option-based editor is on', async () => {
        const unmount = await render(undefined, {isOptionBasedFormEditorEnabled: true, adminUrl: '/wp-admin/'});

        const {legacyView} = mockPageProps.mock.calls[0][0];
        expect(typeof legacyView.onSwitched).toBe('function');
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await unmount();
    });

    it('goes to the legacy Forms list after the switch', async () => {
        const unmount = await render(undefined, {isOptionBasedFormEditorEnabled: true, adminUrl: '/wp-admin/'});
        const {legacyView} = mockPageProps.mock.calls[0][0];

        // jsdom cannot navigate, so swap the location for a plain object that records the address.
        const originalLocation = window.location;
        Object.defineProperty(window, 'location', {configurable: true, value: {href: ''}});

        try {
            legacyView.onSwitched();

            expect(window.location.href).toBe('/wp-admin/edit.php?post_type=give_forms');
        } finally {
            Object.defineProperty(window, 'location', {configurable: true, value: originalLocation});
            await unmount();
        }
    });

    it('offers no "Switch to Legacy View" when the option-based editor is off', async () => {
        const unmount = await render(undefined, {isOptionBasedFormEditorEnabled: false});

        expect(mockPageProps.mock.calls[0][0].legacyView).toBeUndefined();

        await unmount();
    });

    it('offers no "Switch to Legacy View" in a campaign\'s Forms tab', async () => {
        const unmount = await render(5, {isOptionBasedFormEditorEnabled: true});

        expect(mockPageProps.mock.calls[0][0].legacyView).toBeUndefined();
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await unmount();
    });
});
