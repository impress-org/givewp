import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ResultCountStatus, {type ResultCountStatusProps} from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const render = (props: Partial<ResultCountStatusProps>) =>
    act(() =>
        root.render(
            <ResultCountStatus
                requestKey="first"
                isLoading={false}
                totalItems={undefined}
                singleName="donation"
                pluralName="donations"
                {...props}
            />
        )
    );

const region = () => container.querySelector('[role="status"]')!;
const wait = (ms: number) => act(() => jest.advanceTimersByTime(ms));

beforeEach(() => {
    jest.useFakeTimers();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
});

describe('ResultCountStatus', () => {
    it('is one polite, atomic status region with no visible text', async () => {
        await render({});

        expect(container.querySelectorAll('[role="status"]')).toHaveLength(1);
        expect(region().getAttribute('aria-live')).toBe('polite');
        expect(region().getAttribute('aria-atomic')).toBe('true');
        expect(region().className).toBe('screen-reader-text');
    });

    it('says nothing on the first load', async () => {
        await render({isLoading: true});
        await render({isLoading: false, totalItems: 40});
        await wait(1000);

        expect(region().textContent).toBe('');
    });

    it('says the new count once the list settles after a change', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', isLoading: true, totalItems: 40});
        await wait(1000);

        expect(region().textContent).toBe('');

        await render({requestKey: 'second', isLoading: false, totalItems: 12});

        expect(region().textContent).toBe('');

        await wait(300);

        expect(region().textContent).toBe('12 donations found');
    });

    it('uses the singular noun for one result and a fixed text for none', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', totalItems: 1});
        await wait(300);

        expect(region().textContent).toBe('1 donation found');

        await render({requestKey: 'third', totalItems: 0});
        await wait(300);

        expect(region().textContent).toBe('No donations found');
    });

    it('says only the last result when changes come fast', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', totalItems: 5});
        await wait(100);
        await render({requestKey: 'third', totalItems: 7});
        await wait(300);

        expect(region().textContent).toBe('7 donations found');
    });

    it('says the count again when a new result has the same count', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', totalItems: 12});
        await wait(300);
        const first = region().textContent;
        await render({requestKey: 'third', totalItems: 12});
        await wait(300);

        expect(region().textContent?.trim()).toBe('12 donations found');
        expect(region().textContent).not.toBe(first);
    });

    it('says the count after a change when the first load failed', async () => {
        await render({isLoading: true});
        await render({isLoading: false, totalItems: undefined});
        await render({requestKey: 'second', isLoading: true});
        await render({requestKey: 'second', isLoading: false, totalItems: 12});
        await wait(300);

        expect(region().textContent).toBe('12 donations found');
    });

    it('waits while a cached result revalidates, then says the fresh count once', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', isValidating: true, totalItems: 12});
        await wait(1000);

        expect(region().textContent).toBe('');

        await render({requestKey: 'second', isValidating: false, totalItems: 15});
        await wait(300);

        expect(region().textContent).toBe('15 donations found');

        await render({requestKey: 'second', isValidating: false, totalItems: 15});
        await wait(1000);

        expect(region().textContent).toBe('15 donations found');
    });

    it('says a cached count once when revalidation confirms it', async () => {
        await render({totalItems: 40});
        await render({requestKey: 'second', isValidating: true, totalItems: 12});
        await wait(1000);
        await render({requestKey: 'second', isValidating: false, totalItems: 12});
        await wait(1000);

        expect(region().textContent).toBe('12 donations found');
    });

    it('says nothing when a refresh keeps the same view', async () => {
        await render({totalItems: 40});
        await render({totalItems: 39});
        await wait(1000);

        expect(region().textContent).toBe('');
    });
});
