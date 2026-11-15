import {act} from 'react';
import {createRoot} from 'react-dom/client';
import Balloon from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const render = async (element: JSX.Element) => {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const root = createRoot(container);
    await act(() => root.render(element));

    return {
        container,
        unmount: async () => {
            await act(() => root.unmount());
            container.remove();
        },
    };
};

describe('Balloon', () => {
    it('is a region named by its title, with the message, the link and a named close button', async () => {
        const onDismiss = jest.fn();
        const {container, unmount} = await render(
            <Balloon
                className="placement"
                title="Campaign List"
                link={{href: 'https://example.com/docs', text: 'Read documentation'}}
                onDismiss={onDismiss}
            >
                The message.
            </Balloon>
        );

        const balloon = container.firstElementChild as HTMLElement;
        expect(balloon.tagName).toBe('SECTION');
        expect(balloon.className).toBe('givewp-balloon placement');
        expect(balloon.getAttribute('role')).toBeNull();

        const heading = balloon.querySelector('h3') as HTMLElement;
        expect(heading.textContent).toBe('Campaign List');
        expect(balloon.getAttribute('aria-labelledby')).toBe(heading.id);
        expect(balloon.textContent).toContain('The message.');

        const link = balloon.querySelector('a') as HTMLAnchorElement;
        expect(link.getAttribute('href')).toBe('https://example.com/docs');
        expect(link.textContent).toContain('Read documentation');

        const close = balloon.querySelector('button') as HTMLButtonElement;
        expect(close).toBe(balloon.firstElementChild);
        expect(close.getAttribute('type')).toBe('button');
        expect(close.getAttribute('aria-label')).toBe('Dismiss');
        expect(close.className).toContain('is-small');

        await act(async () => close.click());
        expect(onDismiss).toHaveBeenCalledTimes(1);

        await unmount();
    });

    it('is a plain box with its actions and no close button when it has no title and no onDismiss', async () => {
        const {container, unmount} = await render(
            <Balloon actions={<button type="button">Got it</button>}>The message.</Balloon>
        );

        const balloon = container.firstElementChild as HTMLElement;
        expect(balloon.tagName).toBe('DIV');
        expect(balloon.hasAttribute('aria-labelledby')).toBe(false);
        expect(balloon.querySelector('h3')).toBeNull();
        expect(balloon.querySelector('[aria-label="Dismiss"]')).toBeNull();
        expect(balloon.querySelector('.givewp-balloon__actions')?.textContent).toBe('Got it');

        await unmount();
    });
});
