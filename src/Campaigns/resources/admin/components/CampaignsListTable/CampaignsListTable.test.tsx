import {act} from 'react';
import {createRoot} from 'react-dom/client';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockDismiss = jest.fn();
const mockPageProps = jest.fn();

// A stand-in for the page: it renders its children, where the Campaign List balloon sits.
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
    it('shows the Campaign List tip as a floating balloon, and dismisses it', async () => {
        (window as unknown as Record<string, unknown>).GiveCampaignsListTable = {pluginUrl: ''};
        const {default: CampaignsListTable} = await import('./index');
        const container = document.createElement('div');
        document.body.appendChild(container);
        const root = createRoot(container);

        await act(() => root.render(<CampaignsListTable />));

        // The page gets no notices: the tip is a child of the page, not an entry in the notice area.
        expect(mockPageProps.mock.calls[0][0].notices).toBeUndefined();

        const heading = container.querySelector('h3') as HTMLElement;
        expect(heading.textContent).toBe('Campaign List');
        const balloon = heading.closest('div')!.parentElement as HTMLElement;
        expect(balloon.querySelector('a')?.getAttribute('href')).toBe('https://docs.givewp.com/campaigns-docs');
        expect(balloon.querySelector('[role="group"]')).toBeNull();

        // The close control is the balloon's first child.
        await act(() => (balloon.firstElementChild as HTMLElement).click());

        expect(mockDismiss).toHaveBeenCalledWith('givewp_campaign_listtable_notice');
        expect(container.querySelector('h3')).toBeNull();

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
