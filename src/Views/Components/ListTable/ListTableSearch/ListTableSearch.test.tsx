import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTableSearch from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onChange = jest.fn();

const render = (props: Partial<React.ComponentProps<typeof ListTableSearch>> = {}) =>
    act(() =>
        root.render(
            <ListTableSearch
                label="Search donors"
                hint="Searches name and email."
                value=""
                onChange={onChange}
                {...props}
            />
        )
    );

const input = () => container.querySelector<HTMLInputElement>('input')!;

// Sets the value the way a keystroke does, so React sees the change.
const type = (text: string) =>
    act(() => {
        const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
        setter.call(input(), text);
        input().dispatchEvent(new Event('input', {bubbles: true}));
    });

beforeEach(() => {
    jest.useFakeTimers();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onChange.mockClear();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
});

describe('ListTableSearch', () => {
    it('shows the label as visible text', async () => {
        await render();

        const label = container.querySelector('label');

        expect(label?.textContent).toBe('Search donors');
        expect(label?.classList.contains('screen-reader-text')).toBe(false);
        expect(label?.getAttribute('for')).toBe(input().id);
    });

    it('links the hint to the input', async () => {
        await render();

        const hint = document.getElementById(input().getAttribute('aria-describedby') ?? '');

        expect(hint?.textContent).toBe('Searches name and email.');
    });

    it('calls onChange once after typing stops', async () => {
        await render();

        await type('j');
        await type('jo');
        await type('joe');

        expect(onChange).not.toHaveBeenCalled();

        await act(() => jest.advanceTimersByTime(500));

        expect(onChange).toHaveBeenCalledTimes(1);
        expect(onChange).toHaveBeenCalledWith('joe');
    });

    it('follows a new value from props', async () => {
        await render({value: 'abc'});
        expect(input().value).toBe('abc');

        await render({value: ''});
        expect(input().value).toBe('');
    });

    it('hides the hint element when hint is empty', async () => {
        await render({hint: ''});

        expect(input().hasAttribute('aria-describedby')).toBe(false);
        expect(container.querySelector('.givewp-list-table-search__hint')).toBeNull();
    });
});
