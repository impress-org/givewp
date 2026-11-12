import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import InterweaveSSR from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const render = (item: Record<string, unknown>, columnId: string) =>
    act(() => root.render(<InterweaveSSR column={{id: columnId}} item={item} />));

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('InterweaveSSR', () => {
    it('renders an empty cell as the dash with a label', async () => {
        await render({id: 1, amount: ''}, 'amount');

        expect(container.textContent).toBe('—No value');
        expect(container.querySelector('[aria-hidden="true"]')?.textContent).toBe('—');
    });

    it('keeps the server dash markup through Interweave', async () => {
        const html =
            '<span class="givewp-list-table-dash"><span aria-hidden="true">—</span><span class="screen-reader-text">No date</span></span>';

        await render({id: 1, date: html}, 'date');

        expect(container.querySelector('.givewp-list-table-dash [aria-hidden="true"]')).not.toBeNull();
        expect(container.querySelector('.screen-reader-text')?.textContent).toBe('No date');
    });

    it('keeps the truncated text wrapper and its title', async () => {
        const html = '<span class="givewp-truncated-text" title="Full name"><a href="#">Full name</a></span>';

        await render({id: 1, formCampaign: html}, 'formCampaign');

        expect(container.querySelector('.givewp-truncated-text')?.getAttribute('title')).toBe('Full name');
    });
});
