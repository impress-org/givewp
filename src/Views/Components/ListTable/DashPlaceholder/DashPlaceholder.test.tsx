import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import DashPlaceholder from './index';

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

describe('DashPlaceholder', () => {
    it('renders the same markup as the PHP Placeholder::dash()', async () => {
        await act(() => root.render(<DashPlaceholder label="No date" />));

        expect(container.innerHTML).toBe(
            '<span class="givewp-list-table-dash"><span aria-hidden="true">—</span><span class="screen-reader-text">No date</span></span>'
        );
    });
});
