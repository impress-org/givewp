import {act} from 'react';
import {createRoot} from 'react-dom/client';
import UpgradedFormTooltip from './UpgradedFormTooltip';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

describe('UpgradedFormTooltip', () => {
    it('shows the message in a balloon and hides it on "Got it"', async () => {
        const onDismiss = jest.fn();
        const container = document.createElement('div');
        const root = createRoot(container);
        await act(() => root.render(<UpgradedFormTooltip onDismiss={onDismiss} />));

        const balloon = container.querySelector('.givewp-balloon') as HTMLElement;
        expect(balloon.textContent).toContain('already associated with an upgraded form');
        expect(balloon.querySelector('[aria-label="Dismiss"]')).toBeNull();

        const gotIt = balloon.querySelector('.givewp-balloon__actions button') as HTMLButtonElement;
        expect(gotIt.textContent).toBe('Got it');
        expect(gotIt.className).toContain('is-secondary');

        await act(async () => gotIt.click());
        expect(onDismiss).toHaveBeenCalledTimes(1);
        expect(container.querySelector('.givewp-balloon')).toBeNull();

        await act(() => root.unmount());
    });
});
