import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import useDelayedFlag from './useDelayedFlag';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

function Probe({active}: {active: boolean}) {
    return <span>{String(useDelayedFlag(active, 300))}</span>;
}

const render = (active: boolean) => act(() => root.render(<Probe active={active} />));
const text = () => container.textContent;

beforeEach(() => {
    jest.useFakeTimers();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
});

describe('useDelayedFlag', () => {
    it('stays off for a short wait', async () => {
        await render(true);
        await act(() => jest.advanceTimersByTime(299));

        expect(text()).toBe('false');
    });

    it('turns on after the delay', async () => {
        await render(true);
        await act(() => jest.advanceTimersByTime(300));

        expect(text()).toBe('true');
    });

    it('turns off at once when the wait ends', async () => {
        await render(true);
        await act(() => jest.advanceTimersByTime(300));
        await render(false);

        expect(text()).toBe('false');
    });

    it('never turns on when the wait ends before the delay', async () => {
        await render(true);
        await act(() => jest.advanceTimersByTime(200));
        await render(false);
        await act(() => jest.advanceTimersByTime(500));

        expect(text()).toBe('false');
    });
});
