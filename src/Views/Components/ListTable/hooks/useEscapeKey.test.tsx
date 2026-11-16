import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import useEscapeKey from './useEscapeKey';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const onEscape = jest.fn();
let container: HTMLDivElement;
let root: Root;

function Probe({enabled}: {enabled: boolean}) {
    useEscapeKey(onEscape, enabled);

    return null;
}

const press = (target: EventTarget = document.body, key = 'Escape') =>
    act(() => {
        target.dispatchEvent(new KeyboardEvent('keydown', {key, bubbles: true, cancelable: true}));
    });

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onEscape.mockClear();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    document.body.innerHTML = '';
});

describe('useEscapeKey', () => {
    it('runs the handler on Esc anywhere on the page while enabled', async () => {
        await act(() => root.render(<Probe enabled />));

        await press();

        expect(onEscape).toHaveBeenCalledTimes(1);
    });

    it('ignores other keys and does nothing while disabled', async () => {
        await act(() => root.render(<Probe enabled />));
        await press(document.body, 'Enter');
        expect(onEscape).not.toHaveBeenCalled();

        await act(() => root.render(<Probe enabled={false} />));
        await press();
        expect(onEscape).not.toHaveBeenCalled();
    });

    it('leaves Esc to a popover, a menu or a dialog that is open, and to a handler that used it', async () => {
        await act(() => root.render(<Probe enabled />));

        const dialog = document.createElement('div');
        dialog.setAttribute('role', 'dialog');
        document.body.appendChild(dialog);
        await press();
        expect(onEscape).not.toHaveBeenCalled();
        dialog.remove();

        const prevent = (event: Event) => event.preventDefault();
        document.body.addEventListener('keydown', prevent);
        await press();
        document.body.removeEventListener('keydown', prevent);
        expect(onEscape).not.toHaveBeenCalled();
    });

    it('stops listening after unmount', async () => {
        await act(() => root.render(<Probe enabled />));
        await act(() => root.render(<div />));

        await press();

        expect(onEscape).not.toHaveBeenCalled();
    });
});
