import {act} from 'react';
import {createRoot} from 'react-dom/client';
import CampaignNotice from './CampaignNotice';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

// The details page tips (Settings and Overview tabs) keep the default Balloon: the same root, the same
// placement class, a close button first, then the title, the message and the link.
describe('CampaignNotice on the campaign details page', () => {
    it('renders the default Balloon with its placement class, unchanged', async () => {
        const onDismiss = jest.fn();
        const container = document.createElement('div');
        const root = createRoot(container);

        await act(() =>
            root.render(
                <CampaignNotice
                    title="Settings"
                    description="Tune the campaign."
                    type="campaignSettings"
                    linkText="Read more"
                    linkHref="https://example.com"
                    handleDismiss={onDismiss}
                />
            )
        );

        const balloon = container.firstElementChild as HTMLElement;
        expect(balloon.tagName).toBe('DIV');
        expect(balloon.getAttribute('role')).toBe('group');
        expect(balloon.className).toMatch(/^givewp-balloon /);
        expect(balloon.className.split(' ').length).toBe(3);
        expect((balloon.firstElementChild as HTMLElement).getAttribute('aria-label')).toBe('Dismiss');
        expect(balloon.querySelector('h3')?.textContent).toBe('Settings');
        expect(balloon.querySelector('a')?.getAttribute('href')).toBe('https://example.com');

        await act(() => (balloon.firstElementChild as HTMLElement).click());
        expect(onDismiss).toHaveBeenCalledTimes(1);

        await act(() => root.unmount());
    });
});
