import {act, useState} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import EntityFilterControl, {type EntityFilterControlProps} from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const options = [
    {value: '12', label: 'Spring appeal'},
    {value: '15', label: 'Spring gala'},
];

let container: HTMLDivElement;
let root: Root;
const onChange = jest.fn();
const onSearch = jest.fn();
const onLoadMore = jest.fn();
const onRetry = jest.fn();

const baseProps: EntityFilterControlProps = {
    label: 'Campaign',
    options,
    value: [],
    multiple: true,
    search: '',
    onSearch,
    isLoading: false,
    hasMore: false,
    onLoadMore,
    onRetry,
    onChange,
};

// Keeps the value like the bar does, so a second click builds on the first.
function Stateful(props: Partial<EntityFilterControlProps>) {
    const [value, setValue] = useState<string[]>(props.value ?? []);

    return (
        <EntityFilterControl
            {...baseProps}
            {...props}
            value={value}
            onChange={(next) => {
                onChange(next);
                setValue(next);
            }}
        />
    );
}

const render = (props: Partial<EntityFilterControlProps> = {}) => act(() => root.render(<Stateful {...props} />));
const inputNamed = (name: string) =>
    Array.from(container.querySelectorAll<HTMLInputElement>('input')).find(
        (input) => container.querySelector(`label[for="${input.id}"]`)?.textContent === name
    )!;
const buttonNamed = (name: string) =>
    Array.from(container.querySelectorAll<HTMLButtonElement>('button')).find((button) => button.textContent === name)!;
const click = (element: HTMLElement) => act(() => element.click());

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    jest.clearAllMocks();
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
});

describe('EntityFilterControl', () => {
    it('shows a labeled spinner while loading and the search box stays focusable', () => {
        render({isLoading: true});

        const status = container.querySelector('[role="status"]')!;
        expect(status.textContent).toContain('Loading');
        const search = container.querySelector<HTMLInputElement>('input[type="search"]')!;
        expect(search.disabled).toBe(false);
        search.focus();
        expect(document.activeElement).toBe(search);
    });

    it('a failed load shows the message and a Retry button that calls onRetry', () => {
        render({error: 'Could not load campaigns.'});

        expect(container.querySelector('[role="alert"]')!.textContent).toContain('Could not load campaigns.');
        click(buttonNamed('Retry'));

        expect(onRetry).toHaveBeenCalledTimes(1);
    });

    it('multiple: selecting two ids reports both', () => {
        render();
        click(inputNamed('Spring appeal'));
        click(inputNamed('Spring gala'));

        expect(onChange).toHaveBeenLastCalledWith(['12', '15']);
    });

    it('single: selecting replaces the value', () => {
        render({multiple: false, value: ['12']});
        click(inputNamed('Spring gala'));

        expect(onChange).toHaveBeenLastCalledWith(['15']);
    });

    it('the none option comes first', () => {
        render({noneOption: {value: 'none', label: 'No campaign'}});

        const labels = Array.from(container.querySelectorAll('label')).map((label) => label.textContent);
        expect(labels.filter((text) => text !== 'Search Campaign').slice(0, 3)).toEqual([
            'No campaign',
            'Spring appeal',
            'Spring gala',
        ]);
    });

    it('none is exclusive of specific values', () => {
        render({noneOption: {value: 'none', label: 'No campaign'}, value: ['12']});
        click(inputNamed('No campaign'));
        expect(onChange).toHaveBeenLastCalledWith(['none']);

        click(inputNamed('Spring gala'));
        expect(onChange).toHaveBeenLastCalledWith(['15']);
    });

    it('"Load more" calls onLoadMore', () => {
        render({hasMore: true});
        click(buttonNamed('Load more'));

        expect(onLoadMore).toHaveBeenCalledTimes(1);
    });

    it('reports typing to onSearch', () => {
        render();
        const search = container.querySelector<HTMLInputElement>('input[type="search"]')!;
        act(() => {
            const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
            setter.call(search, 'spr');
            search.dispatchEvent(new Event('input', {bubbles: true}));
        });

        expect(onSearch).toHaveBeenCalledWith('spr');
    });

    it('labels render as text, never markup', () => {
        render({options: [{value: '1', label: '<b>x</b>'}]});

        expect(container.querySelector('b')).toBeNull();
        expect(container.textContent).toContain('<b>x</b>');
    });

    it('no options and no error shows "No matches"', () => {
        render({options: []});

        expect(container.textContent).toContain('No matches');
    });

    it('does not say "No matches" while loading or after an error', () => {
        render({options: [], isLoading: true});
        expect(container.textContent).not.toContain('No matches');

        render({options: [], error: 'Failed'});
        expect(container.textContent).not.toContain('No matches');
    });

    it('names the search box differently from the option list', () => {
        render();

        expect(container.querySelector('label')?.textContent).toBe('Search Campaign');
        expect(container.querySelector('[role="group"]')?.getAttribute('aria-label')).toBe('Campaign');
    });

    it('keeps one live region mounted and changes its text', async () => {
        await render({options: [], isLoading: true});
        const region = container.querySelector('[role="status"]');

        expect(region?.textContent).toContain('Loading');

        await render({options: [], isLoading: false});

        expect(container.querySelector('[role="status"]')).toBe(region);
        expect(region?.textContent).toContain('No matches');
    });

    it('keeps Load more focusable while it loads and ignores clicks', async () => {
        await render({hasMore: true, isLoading: true});
        const more = buttonNamed('Load more');

        expect(more.disabled).toBe(false);
        expect(more.getAttribute('aria-disabled')).toBe('true');

        await click(more);
        expect(onLoadMore).not.toHaveBeenCalled();

        await render({hasMore: true, isLoading: false});
        await click(buttonNamed('Load more'));
        expect(onLoadMore).toHaveBeenCalledTimes(1);
    });
});
