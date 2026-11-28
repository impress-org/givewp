import {act} from 'react';
import {createRoot} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockDismiss = jest.fn();
const mockPageProps = jest.fn();

// A stand-in for the page: it records its props, where the Campaign List notice is passed.
jest.mock('@givewp/components', () => ({
    ListTablePage: (props: {children?: React.ReactNode}) => {
        mockPageProps(props);

        return <div>{props.children}</div>;
    },
}));
jest.mock('@givewp/campaigns/utils', () => ({
    getCampaignOptionsWindowData: () => ({
        admin: {showExistingUserIntroNotice: false, showCampaignListTableNotice: true},
    }),
    handleTooltipDismiss: (...args: unknown[]) => mockDismiss(...args),
}));
jest.mock('../CreateCampaignModal', () => () => null);
jest.mock('../MergeCampaign/Modal', () => () => null);
jest.mock('@givewp/campaigns/admin/components/ExistingUserIntroModal', () => () => null);
jest.mock('./CampaignsRowActions', () => ({campaignsRowActions: []}));

describe('CampaignsListTable', () => {
    it('passes the Campaign List tip as an onboarding notice for the notice area, and dismisses it', async () => {
        (window as unknown as Record<string, unknown>).GiveCampaignsListTable = {pluginUrl: ''};
        const {default: CampaignsListTable} = await import('./index');
        const container = document.createElement('div');
        document.body.appendChild(container);
        const root = createRoot(container);

        await act(() => root.render(<CampaignsListTable />));

        // The tip is no longer a floating balloon inside the page.
        expect(container.querySelector('.givewp-balloon')).toBeNull();

        const {notices} = mockPageProps.mock.calls[0][0];
        expect(notices).toHaveLength(1);
        expect(notices[0]).toMatchObject({
            id: 'campaign-list',
            kind: 'onboarding',
            title: 'Campaign List',
            link: {href: 'https://docs.givewp.com/campaigns-docs', opensInNewTab: true},
        });

        await act(() => notices[0].onDismiss());

        expect(mockDismiss).toHaveBeenCalledWith('givewp_campaign_listtable_notice');
        expect(mockPageProps.mock.calls.at(-1)[0].notices).toEqual([]);

        await act(() => root.unmount());
        container.remove();
    });

    it('offers no "Switch to Legacy View": Campaigns has no legacy screen', async () => {
        (window as unknown as Record<string, unknown>).GiveCampaignsListTable = {pluginUrl: ''};
        const {default: CampaignsListTable} = await import('./index');
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() => root.render(<CampaignsListTable />));

        expect(mockPageProps).toHaveBeenCalled();
        expect(mockPageProps.mock.calls[0][0].legacyView).toBeUndefined();
        expect(container.textContent).not.toContain('Switch to Legacy View');

        await act(() => root.unmount());
    });
});
