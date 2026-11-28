import {act, startTransition, Suspense, useState} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTableSearch, {type ListTableSearchProps} from './index';
import SearchHint from './SearchHint';
import {shortHint} from './shortHint';
import useSearchHint from './useSearchHint';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onChange = jest.fn();

type HarnessProps = Partial<ListTableSearchProps> & {hint?: string};

/**
 * The search box and its hint as the list places them: the box in the toolbar row, other controls
 * after it, and the hint under the row.
 */
function Harness({hint = 'Searches name and email.', ...props}: HarnessProps) {
    const searchHint = useSearchHint(hint);

    return (
        <>
            <div className="row">
                <ListTableSearch
                    label="Search donors"
                    value=""
                    onChange={onChange}
                    describedBy={searchHint.describedBy}
                    {...props}
                />
                <button type="button">Status</button>
            </div>
            {searchHint.hint && <SearchHint {...searchHint.hint} />}
        </>
    );
}

const render = (props: HarnessProps = {}) => act(() => root.render(<Harness {...props} />));

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

    it('renders only the box: the hint is placed apart, under the row', async () => {
        await render();

        expect(container.querySelector('.givewp-list-table-search .givewp-list-table-search__hint')).toBeNull();
        expect(container.querySelector('.row + .givewp-list-table-search__hint-row')).not.toBeNull();
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

    it('still reports the term when the parent passes a new callback mid-delay', async () => {
        const first = jest.fn();
        const second = jest.fn();

        await render({onChange: first});
        await type('jo');
        await act(() => jest.advanceTimersByTime(100));
        await render({onChange: second});
        await act(() => jest.advanceTimersByTime(500));

        expect(first).not.toHaveBeenCalled();
        expect(second).toHaveBeenCalledTimes(1);
        expect(second).toHaveBeenCalledWith('jo');
    });

    it('reports with the committed callback when a render with a new callback is thrown away', async () => {
        const first = jest.fn();
        const second = jest.fn();
        const never = new Promise<void>(() => {});
        let update: (next: {callback: () => void; suspend: boolean}) => void = () => {};

        const Gate = ({suspend}: {suspend: boolean}) => {
            if (suspend) {
                throw never;
            }
            return null;
        };
        const Parent = () => {
            const [state, setState] = useState({callback: first, suspend: false});
            update = setState;
            return (
                <Suspense fallback={null}>
                    <ListTableSearch label="Search donors" value="" onChange={state.callback} />
                    <Gate suspend={state.suspend} />
                </Suspense>
            );
        };

        await act(() => root.render(<Parent />));
        await type('jo');
        // A transition that suspends keeps the old screen, so React throws this render away.
        await act(() => startTransition(() => update({callback: second, suspend: true})));
        await act(() => jest.advanceTimersByTime(500));

        expect(second).not.toHaveBeenCalled();
        expect(first).toHaveBeenCalledWith('jo');
    });

    it('keeps newer typing when the parent echoes an older reported term', async () => {
        await render();
        await type('joe');
        await act(() => jest.advanceTimersByTime(500));
        await type('joey');

        await render({value: 'joe'});

        expect(input().value).toBe('joey');
    });

    it('drops a pending search when the view gets a new term from outside', async () => {
        await render({value: 'joe'});
        await type('joey');
        await act(() => jest.advanceTimersByTime(100));

        await render({value: ''});
        await act(() => jest.advanceTimersByTime(500));

        expect(onChange).not.toHaveBeenCalled();
        expect(input().value).toBe('');
    });
});

describe('shortHint', () => {
    it('keeps the first sentence of the Donations hint', () => {
        expect(
            shortHint('Searches donor first name, last name, email or donation ID. A name matches from the start.')
        ).toBe('Searches donor first name, last name, email or donation ID.');
    });

    it('keeps a one-sentence hint whole', () => {
        expect(shortHint('Searches titles.')).toBe('Searches titles.');
    });

    it('ignores trailing whitespace', () => {
        expect(shortHint('Searches titles. ')).toBe('Searches titles.');
    });

    it('splits after a question mark or an exclamation mark', () => {
        expect(shortHint('Which fields? Name and email.')).toBe('Which fields?');
        expect(shortHint('Type a name! Then wait.')).toBe('Type a name!');
    });

    it('keeps a hint with no final punctuation whole', () => {
        expect(shortHint('Searches titles')).toBe('Searches titles');
    });

    it('returns an empty hint as empty', () => {
        expect(shortHint('')).toBe('');
    });
});

describe('ListTableSearch hint row', () => {
    const long = 'Searches donor name or ID. An ID matches exactly.';
    const hintText = () => container.querySelector('.givewp-list-table-search__hint')?.textContent;
    const helpButton = () => container.querySelector<HTMLButtonElement>('.givewp-help-tip__button');

    it('shows the short line and a help button that carries the full rule', async () => {
        await render({hint: long});

        expect(hintText()).toBe('Searches donor name or ID.');
        expect(helpButton()?.getAttribute('aria-label')).toBe('How search works');

        // The input is described by the FULL rule, in text only a screen reader gets.
        const described = document.getElementById(input().getAttribute('aria-describedby') ?? '');

        expect(described?.textContent).toBe(long);
        expect(described?.className).toContain('screen-reader-text');
    });

    it('has no help button when the hint is one sentence', async () => {
        await render({hint: 'Searches titles.'});

        expect(helpButton()).toBeNull();
        expect(hintText()).toBe('Searches titles.');
        expect(input().getAttribute('aria-describedby')).toBe(
            container.querySelector('.givewp-list-table-search__hint')?.id
        );
    });

    it('renders no hint row for an empty hint (legacy screens)', async () => {
        await render({hint: ''});

        expect(hintText()).toBeUndefined();
        expect(helpButton()).toBeNull();
    });
});
