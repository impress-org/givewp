import {act} from 'react';
import {createRoot} from 'react-dom/client';
import MockNoticeArea from '@givewp/components/ListTable/ListTableNoticeArea';
import type {ListTableNotice} from '@givewp/components/ListTable/notices/types';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockDismiss = jest.fn();
const mockPageProps = jest.fn();

// A stand-in for the page: the real notice area, then a list root with a select-all checkbox.
jest.mock('@givewp/components', () => ({
    ListTablePage: (props: {notices?: ListTableNotice[]; children?: React.ReactNode}) => {
        mockPageProps(props);
        const {notices, children} = props;

        return (
            <div>
                <MockNoticeArea notices={notices ?? []} recommendation={null} onDismissRecommendation={() => {}} />
                <div data-testid="list-root">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" data-testid="select-all" />
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                {children}
            </div>
        );
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
    it('shows the Campaign List tip as a notice outside the list, and dismisses it', async () => {
        (window as unknown as Record<string, unknown>).GiveCampaignsListTable = {pluginUrl: ''};
        const {default: CampaignsListTable} = await import('./index');
        const container = document.createElement('div');
        document.body.appendChild(container);
        const root = createRoot(container);

        await act(() => root.render(<CampaignsListTable />));

        const group = container.querySelector('[role="group"]') as HTMLElement;
        expect(group).not.toBeNull();
        expect(group.textContent).toContain('Campaign List');
        expect(group.querySelector('a')?.getAttribute('href')).toBe('https://docs.givewp.com/campaigns-docs');
        expect(group.closest('table')).toBeNull();
        expect(group.contains(container.querySelector('[data-testid="select-all"]'))).toBe(false);
        expect(container.querySelector('[data-testid="list-root"]')?.contains(group)).toBe(false);

        const dismiss = group.querySelector('button') as HTMLButtonElement;
        await act(() => dismiss.click());

        expect(mockDismiss).toHaveBeenCalledWith('givewp_campaign_listtable_notice');
        expect(container.querySelector('[role="group"]')).toBeNull();

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
