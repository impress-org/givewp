import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import Skeleton from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('Skeleton', () => {
    it('is hidden from assistive technology', async () => {
        await act(() => root.render(<Skeleton />));

        const skeleton = container.querySelector('.givewp-skeleton') as HTMLElement;

        expect(skeleton.getAttribute('aria-hidden')).toBe('true');
        expect(skeleton.textContent).toBe('');
    });

    it('applies the given sizes, and fills the width by default', async () => {
        await act(() => root.render(<Skeleton inlineSize="5ch" blockSize="2rem" className="extra" />));

        const skeleton = container.querySelector('.givewp-skeleton') as HTMLElement;

        expect(skeleton.style.inlineSize).toBe('5ch');
        expect(skeleton.style.blockSize).toBe('2rem');
        expect(skeleton.classList.contains('extra')).toBe(true);

        await act(() => root.render(<Skeleton />));

        const fallback = container.querySelector('.givewp-skeleton') as HTMLElement;

        expect(fallback.style.inlineSize).toBe('100%');
        expect(fallback.style.blockSize).toBe('1em');
    });
});
