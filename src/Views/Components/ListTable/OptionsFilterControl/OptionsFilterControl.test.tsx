import {act, useState} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import OptionsFilterControl from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const options = [
    {value: 'publish', label: 'Active'},
    {value: 'pending', label: 'Pending'},
    {value: 'draft', label: 'Draft'},
];

let container: HTMLDivElement;
let root: Root;
const onChange = jest.fn();

// Keeps the value like the bar does, so a second click builds on the first.
function Stateful({multiple, initial}: {multiple: boolean; initial: string[]}) {
    const [value, setValue] = useState(initial);

    return (
        <OptionsFilterControl
            label="Status"
            options={options}
            value={value}
            multiple={multiple}
            onChange={(next) => {
                onChange(next);
                setValue(next);
            }}
        />
    );
}

const render = (multiple: boolean, initial: string[] = []) =>
    act(() => root.render(<Stateful key={`${multiple}-${initial}`} multiple={multiple} initial={initial} />));

const inputNamed = (name: string) =>
    Array.from(container.querySelectorAll<HTMLInputElement>('input')).find(
        (input) => container.querySelector(`label[for="${input.id}"]`)?.textContent === name
    )!;
const click = (element: HTMLElement) => act(() => element.click());

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

describe('OptionsFilterControl', () => {
    it('multiple: checking two options reports both', async () => {
        await render(true);

        await click(inputNamed('Active'));
        await click(inputNamed('Draft'));

        expect(onChange).toHaveBeenCalledTimes(2);
        expect(onChange).toHaveBeenNthCalledWith(1, ['publish']);
        expect(onChange).toHaveBeenLastCalledWith(['publish', 'draft']);
    });

    it('multiple: unchecking removes only that option', async () => {
        await render(true, ['publish', 'draft']);

        await click(inputNamed('Active'));

        expect(onChange).toHaveBeenLastCalledWith(['draft']);
    });

    it('single: choosing one reports only it', async () => {
        await render(false, ['publish']);

        await click(inputNamed('Pending'));

        expect(onChange).toHaveBeenCalledTimes(1);
        expect(onChange).toHaveBeenCalledWith(['pending']);
    });

    it('puts the options in a group named by the label', async () => {
        await render(true);
        expect(container.querySelector('[role="group"]')?.getAttribute('aria-label')).toBe('Status');

        await render(false);
        expect(container.querySelector('[role="radiogroup"]')?.getAttribute('aria-label')).toBe('Status');
    });

    it('shows the checked state from value', async () => {
        await render(true, ['pending']);

        expect(inputNamed('Active').checked).toBe(false);
        expect(inputNamed('Pending').checked).toBe(true);

        await render(false, ['draft']);

        expect(inputNamed('Pending').checked).toBe(false);
        expect(inputNamed('Draft').checked).toBe(true);
    });
});
