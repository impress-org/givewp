import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ModalDialog from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let root: Root;
let container: HTMLDivElement;
let trigger: HTMLButtonElement;

const render = (isOpen: boolean) =>
    act(() =>
        root.render(
            <ModalDialog title="Test dialog" isOpen={isOpen} handleClose={jest.fn()}>
                <button type="button">Inside</button>
            </ModalDialog>
        )
    );

beforeEach(() => {
    trigger = document.createElement('button');
    document.body.appendChild(trigger);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    trigger.remove();
    container.remove();
});

describe('ModalDialog focus', () => {
    it('moves focus into the dialog when it opens and back to the trigger when it closes', async () => {
        await render(false);
        trigger.focus();

        await render(true);

        expect(document.body.querySelector('[role="dialog"]')?.contains(document.activeElement)).toBe(true);

        await render(false);

        expect(document.activeElement).toBe(trigger);
    });
});
