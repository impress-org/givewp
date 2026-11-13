import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTablePageHeader from './ListTablePageHeader';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const render = (element: JSX.Element) => act(() => root.render(element));
const testModeToggle = {label: 'Test mode', ariaLabel: 'View test donations'};

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('ListTablePageHeader', () => {
    it('shows the test switch inside the header, as a switch with its label', async () => {
        await render(
            <ListTablePageHeader
                title="Donations"
                testMode={false}
                onChangeTestMode={jest.fn()}
                testModeToggle={testModeToggle}
            />
        );
        const header = container.querySelector('header') as HTMLElement;
        const toggle = header.querySelector('input[role="switch"]') as HTMLInputElement;

        expect(toggle).not.toBeNull();
        expect(toggle.checked).toBe(false);
        expect(header.textContent).toContain('View test donations');
        expect(container.querySelector('[class*="filtersRow"]')).toBeNull();

        const label = header.querySelector(`label[for="${toggle.id}"]`) as HTMLLabelElement;

        expect(toggle.id).not.toBe('');
        expect(label.textContent).toBe('View test donations');
    });

    it('checks the switch while test mode is on', async () => {
        await render(
            <ListTablePageHeader
                title="Donations"
                testMode
                onChangeTestMode={jest.fn()}
                testModeToggle={testModeToggle}
            />
        );

        expect((container.querySelector('input[role="switch"]') as HTMLInputElement).checked).toBe(true);
    });

    it('calls onChangeTestMode with the new value', async () => {
        const onChange = jest.fn();
        await render(
            <ListTablePageHeader
                title="Donations"
                testMode={false}
                onChangeTestMode={onChange}
                testModeToggle={testModeToggle}
            />
        );
        await act(() => (container.querySelector('input[role="switch"]') as HTMLInputElement).click());

        expect(onChange).toHaveBeenCalledWith(true);
    });

    it('shows the test badge only while test mode is on', async () => {
        await render(
            <ListTablePageHeader
                title="Donations"
                testMode
                onChangeTestMode={jest.fn()}
                testModeToggle={testModeToggle}
            />
        );
        const badge = container.querySelector('.givewp-test-mode-badge') as HTMLElement;

        expect(badge.textContent).toContain('Test mode');
    });

    it('renders no switch and no badge on a screen without test mode', async () => {
        await render(<ListTablePageHeader title="Events" testMode={false} onChangeTestMode={jest.fn()} />);

        expect(container.querySelector('input[role="switch"]')).toBeNull();
        expect(container.querySelector('.givewp-test-mode-badge')).toBeNull();
    });

    it('shows the Give mark, hidden from assistive tech', async () => {
        await render(<ListTablePageHeader title="Donations" testMode={false} onChangeTestMode={jest.fn()} />);

        expect(container.querySelector('header svg.givewp-give-mark[aria-hidden="true"]')).not.toBeNull();
    });

    describe('actions order', () => {
        const renderActions = (withSwitch: boolean, withButtons: boolean) =>
            render(
                <ListTablePageHeader
                    title="Donations"
                    testMode={false}
                    onChangeTestMode={jest.fn()}
                    testModeToggle={withSwitch ? testModeToggle : undefined}
                >
                    {withButtons && <button type="button">Import donations</button>}
                </ListTablePageHeader>
            );
        const divider = () => container.querySelector('.givewp-header-divider');

        it('puts the switch before the header buttons', async () => {
            await renderActions(true, true);
            const toggle = container.querySelector('input[role="switch"]') as HTMLElement;
            const button = container.querySelector('header button') as HTMLElement;

            expect(toggle.compareDocumentPosition(button) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
        });

        it('puts a hidden divider between the switch and the buttons', async () => {
            await renderActions(true, true);

            expect(divider()).not.toBeNull();
            expect(divider()?.getAttribute('aria-hidden')).toBe('true');
        });

        it('has no divider with only the switch', async () => {
            await renderActions(true, false);

            expect(divider()).toBeNull();
        });

        it('has no divider with only the buttons', async () => {
            await renderActions(false, true);

            expect(divider()).toBeNull();
        });
    });

    it('takes no stats: the stat tiles render in the list sheet', async () => {
        const header = (
            // @ts-expect-error The header has no stats props.
            <ListTablePageHeader title="Donations" testMode={false} onChangeTestMode={() => {}} statsConfig={{}} />
        );

        await render(header);

        expect(container.querySelector('.givewp-stat-tiles')).toBeNull();
    });
});
