import {act, useRef} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import useHorizontalOverflow from './useHorizontalOverflow';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

type Metrics = {scrollLeft: number; clientWidth: number; scrollWidth: number};

let metrics: Metrics;
let result: {hasMoreStart: boolean; hasMoreEnd: boolean};
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

function Probe() {
    const rootRef = useRef<HTMLDivElement>(null);
    result = useHorizontalOverflow(rootRef);

    return (
        <div ref={rootRef}>
            <div className="dataviews-wrapper" ref={withMetrics} />
        </div>
    );
}

const render = () => act(async () => root.render(<Probe />));

const scrollTo = (scrollLeft: number) =>
    act(async () => {
        metrics.scrollLeft = scrollLeft;
        container.querySelector('.dataviews-wrapper')?.dispatchEvent(new Event('scroll'));
    });

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
});

describe('useHorizontalOverflow', () => {
    it('says there is more at the end when the content is wider than the container', async () => {
        metrics = {scrollLeft: 0, clientWidth: 500, scrollWidth: 900};
        await render();

        expect(result).toEqual({hasMoreStart: false, hasMoreEnd: true});
    });

    it('says there is more at the start, and none at the end, once scrolled to the end', async () => {
        metrics = {scrollLeft: 0, clientWidth: 500, scrollWidth: 900};
        await render();
        await scrollTo(400);

        expect(result).toEqual({hasMoreStart: true, hasMoreEnd: false});
    });

    it('gives the same answers in right-to-left, where scrollLeft is negative', async () => {
        metrics = {scrollLeft: 0, clientWidth: 500, scrollWidth: 900};
        await render();
        expect(result).toEqual({hasMoreStart: false, hasMoreEnd: true});

        await scrollTo(-200);
        expect(result).toEqual({hasMoreStart: true, hasMoreEnd: true});

        await scrollTo(-400);
        expect(result).toEqual({hasMoreStart: true, hasMoreEnd: false});
    });

    it('says nothing is hidden when the content fits', async () => {
        metrics = {scrollLeft: 0, clientWidth: 900, scrollWidth: 900};
        await render();

        expect(result).toEqual({hasMoreStart: false, hasMoreEnd: false});
    });

    it('checks again when the container is resized', async () => {
        const observers: ResizeObserverCallback[] = [];
        const original = globalThis.ResizeObserver;
        globalThis.ResizeObserver = class {
            constructor(callback: ResizeObserverCallback) {
                observers.push(callback);
            }
            observe() {}
            unobserve() {}
            disconnect() {}
        } as unknown as typeof ResizeObserver;

        try {
            metrics = {scrollLeft: 0, clientWidth: 900, scrollWidth: 900};
            await render();
            expect(result.hasMoreEnd).toBe(false);

            await act(async () => {
                metrics.clientWidth = 600;
                observers.forEach((callback) => callback([], {} as ResizeObserver));
            });
            expect(result.hasMoreEnd).toBe(true);
        } finally {
            globalThis.ResizeObserver = original;
        }
    });
});
