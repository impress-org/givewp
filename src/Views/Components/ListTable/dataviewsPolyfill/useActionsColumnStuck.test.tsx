import {readFileSync} from 'fs';
import {act, useRef} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import useActionsColumnStuck from './useActionsColumnStuck';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const STUCK = 'dataviews-view-table__actions-column--stuck';

let metrics: {scrollLeft: number; clientWidth: number; scrollWidth: number};
let root: Root;
let container: HTMLDivElement;

// The scroll container reads its sizes from `metrics`, which jsdom does not compute.
const withMetrics = (element: HTMLDivElement | null) => {
    if (!element) {
        return;
    }

    (['scrollLeft', 'clientWidth', 'scrollWidth'] as const).forEach((key) =>
        Object.defineProperty(element, key, {configurable: true, get: () => metrics[key]})
    );
};

function Probe({rows}: {rows: number}) {
    const rootRef = useRef<HTMLDivElement>(null);
    useActionsColumnStuck(rootRef);

    return (
        <div ref={rootRef}>
            <div className="dataviews-wrapper" ref={withMetrics}>
                <table className="dataviews-view-table">
                    <tbody>
                        {Array.from({length: rows}, (_, index) => (
                            <tr key={index}>
                                <td className="dataviews-view-table__actions-column--sticky" />
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

const render = (rows: number) => act(async () => root.render(<Probe rows={rows} />));

const scrollTo = (scrollLeft: number) => {
    metrics.scrollLeft = scrollLeft;
    container.querySelector('.dataviews-wrapper')!.dispatchEvent(new Event('scroll'));
};

const stuck = () => Array.from(container.querySelectorAll('td'), (cell) => cell.classList.contains(STUCK));

beforeEach(() => {
    metrics = {scrollLeft: 0, clientWidth: 500, scrollWidth: 500};
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
});

describe('useActionsColumnStuck', () => {
    it('marks the cells of rows that arrive after the table', async () => {
        await render(0);

        metrics.scrollWidth = 900;
        await render(2);

        expect(stuck()).toEqual([true, true]);
    });

    it('follows the scroll at once, with no delay', async () => {
        metrics.scrollWidth = 900;
        await render(1);

        scrollTo(400);
        expect(stuck()).toEqual([false]);

        scrollTo(200);
        expect(stuck()).toEqual([true]);
    });

    it('leaves a table that fits unmarked', async () => {
        await render(1);

        expect(stuck()).toEqual([false]);
    });

    it('is still needed: the pinned DataViews waits 200 ms after a scroll to measure', () => {
        // When this fails the `@wordpress/dataviews` pin moved: check whether the stock line now follows
        // the scroll and late rows by itself, and drop the hook if it does.
        const stock = readFileSync(
            'node_modules/@wordpress/dataviews/build-module/dataviews-layouts/table/use-is-horizontal-scroll-end.js',
            'utf8'
        );

        expect(stock).toMatch(/\}, \[scrollContainerRef, setIsHorizontalScrollEnd\]\),\s*200/);
    });
});
