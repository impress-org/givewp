import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {DateRangeValue} from '../filters/types';
import DateRangeFilterControl from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onChange = jest.fn();

// A readable stand-in for the site format.
const formatDate = (ymd: string) => `<${ymd}>`;

const render = (value?: DateRangeValue) =>
    act(() =>
        root.render(<DateRangeFilterControl label="Date" value={value} formatDate={formatDate} onChange={onChange} />)
    );

const buttons = () => Array.from(container.querySelectorAll<HTMLButtonElement>('button'));
const buttonNamed = (name: string) => buttons().find((button) => button.textContent === name)!;
const inputLabeled = (label: string) => {
    const labelElement = Array.from(container.querySelectorAll('label')).find((l) => l.textContent === label);

    return container.querySelector<HTMLInputElement>(`#${labelElement?.getAttribute('for')}`)!;
};
const alertText = () => container.querySelector('[role="alert"]')?.textContent;
const click = (element: HTMLElement) => act(() => element.click());

// Sets the value the way typing does, so React sees the change.
const type = (input: HTMLInputElement, text: string) =>
    act(() => {
        const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
        setter.call(input, text);
        input.dispatchEvent(new Event('input', {bubbles: true}));
    });

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onChange.mockClear();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('DateRangeFilterControl', () => {
    it('offers the six presets in order', async () => {
        await render();

        const presets = buttons()
            .map((button) => button.textContent)
            .filter((text) => text !== 'Apply');

        expect(presets).toEqual([
            'Last 7 days',
            'Last 30 days',
            'Last 90 days',
            'This month',
            'Last month',
            'This year',
        ]);
    });

    it('marks the chosen preset as pressed', async () => {
        await render({preset: '30'});

        expect(buttonNamed('Last 30 days').getAttribute('aria-pressed')).toBe('true');
        expect(buttonNamed('Last 7 days').getAttribute('aria-pressed')).toBe('false');
    });

    it('reports a chosen preset', async () => {
        await render();

        await click(buttonNamed('Last 30 days'));

        expect(onChange).toHaveBeenCalledWith({preset: '30'});
    });

    it('labels the start and end inputs', async () => {
        await render();

        expect(inputLabeled('Start date').type).toBe('date');
        expect(inputLabeled('End date').type).toBe('date');
    });

    it('reports both days on Apply', async () => {
        await render();

        await type(inputLabeled('Start date'), '2024-03-01');
        await type(inputLabeled('End date'), '2024-03-15');
        await click(buttonNamed('Apply'));

        expect(onChange).toHaveBeenCalledWith({start: '2024-03-01', end: '2024-03-15'});
    });

    it('reports a one-sided range when only the start is set', async () => {
        await render();

        await type(inputLabeled('Start date'), '2024-03-01');
        await click(buttonNamed('Apply'));

        expect(onChange).toHaveBeenCalledWith({start: '2024-03-01'});
    });

    it('announces an end before the start, blocks Apply and reports nothing', async () => {
        await render();

        await type(inputLabeled('Start date'), '2024-03-10');
        await type(inputLabeled('End date'), '2024-03-01');
        await click(buttonNamed('Apply'));

        expect(alertText()).toBe('The end date must be on or after the start date.');
        expect(buttonNamed('Apply').disabled).toBe(true);
        expect(onChange).not.toHaveBeenCalled();
    });

    it('links both inputs to the message while the range is wrong', async () => {
        await render();

        await type(inputLabeled('Start date'), '2024-03-10');
        await type(inputLabeled('End date'), '2024-03-01');

        const alertId = container.querySelector('[role="alert"]')?.id;

        expect(alertId).toBeTruthy();
        for (const label of ['Start date', 'End date']) {
            expect(inputLabeled(label).getAttribute('aria-invalid')).toBe('true');
            expect(inputLabeled(label).getAttribute('aria-describedby')).toBe(alertId);
        }

        await type(inputLabeled('End date'), '2024-03-20');

        for (const label of ['Start date', 'End date']) {
            expect(inputLabeled(label).hasAttribute('aria-invalid')).toBe(false);
            expect(inputLabeled(label).hasAttribute('aria-describedby')).toBe(false);
        }
    });

    it('gives each control its own message id', async () => {
        const second = document.createElement('div');
        document.body.appendChild(second);
        const secondRoot = createRoot(second);

        await render();
        await act(() =>
            secondRoot.render(<DateRangeFilterControl label="Other" formatDate={formatDate} onChange={onChange} />)
        );

        for (const host of [container, second]) {
            const [startInput, endInput] = Array.from(host.querySelectorAll<HTMLInputElement>('input[type="date"]'));
            await type(startInput, '2024-03-10');
            await type(endInput, '2024-03-01');
        }

        const firstId = container.querySelector('[role="alert"]')?.id;
        const secondId = second.querySelector('[role="alert"]')?.id;

        expect(firstId).toBeTruthy();
        expect(secondId).toBeTruthy();
        expect(firstId).not.toBe(secondId);

        await act(() => secondRoot.unmount());
        second.remove();
    });

    it('shows no message while the range is valid', async () => {
        await render();

        await type(inputLabeled('Start date'), '2024-03-01');

        expect(alertText()).toBeUndefined();
    });

    it('shows the chosen days in the site format', async () => {
        await render({start: '2024-03-01', end: '2024-03-15'});

        expect(container.textContent).toContain('<2024-03-01>');
        expect(container.textContent).toContain('<2024-03-15>');
        expect(inputLabeled('Start date').value).toBe('2024-03-01');
    });

    it('keeps a half-typed date when the parent passes an equal but new value object', async () => {
        await render({start: '2024-03-01'});
        await type(inputLabeled('End date'), '2024-03-15');

        await render({start: '2024-03-01'});

        expect(inputLabeled('End date').value).toBe('2024-03-15');
    });

    it('clears a half-typed date when a preset is reset to nothing', async () => {
        await render({preset: '30'});
        await type(inputLabeled('Start date'), '2024-03-01');

        await render(undefined);

        expect(inputLabeled('Start date').value).toBe('');
    });

    it('clears a half-typed date when one preset is replaced by another', async () => {
        await render({preset: '30'});
        await type(inputLabeled('Start date'), '2024-03-01');

        await render({preset: '90'});

        expect(inputLabeled('Start date').value).toBe('');
    });
});
