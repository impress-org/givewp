import {act} from 'react';
import {createRoot} from 'react-dom/client';
import type {ListTableRowAction} from '../ListTablePage/types';
import {rowActionsToActions} from './rowActionsToActions';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const context = () => ({report: jest.fn(), refresh: jest.fn(async () => undefined), clearSelection: jest.fn()});

describe('rowActionsToActions', () => {
    it('keeps the primary flag and eligibility, and never makes a bulk action', () => {
        const isEligible = () => true;
        const [action] = rowActionsToActions(
            [{id: 'edit', label: 'Edit', isPrimary: true, isEligible, href: (item) => `edit?id=${item.id}`}],
            context()
        );

        expect(action).toMatchObject({id: 'edit', label: 'Edit', isPrimary: true, isEligible});
        expect(action.supportsBulk).toBeUndefined();
    });

    it('navigates for an href action', () => {
        const assign = jest.fn();
        const location = window.location;

        // jsdom does not navigate; watch the call instead.
        Object.defineProperty(window, 'location', {value: {...location, assign}, configurable: true});

        const [action] = rowActionsToActions([{id: 'edit', label: 'Edit', href: (item) => `edit?id=${item.id}`}], context());
        'callback' in action && action.callback([{id: 9}], {registry: null});

        expect(assign).toHaveBeenCalledWith('edit?id=9');
        Object.defineProperty(window, 'location', {value: location, configurable: true});
    });

    it('runs an action without confirm at once, then reports and refreshes', async () => {
        const pageContext = context();
        const run = jest.fn(async () => ({errors: [], successes: [9]}));
        const [action] = rowActionsToActions([{id: 'duplicate', label: 'Duplicate', run}], pageContext);

        await ('callback' in action && action.callback([{id: 9}], {registry: null}));

        expect(run).toHaveBeenCalledWith({id: 9});
        expect(pageContext.report).toHaveBeenCalledWith({changed: 1, failed: 0}, 'Duplicate');
        expect(pageContext.refresh).toHaveBeenCalled();
    });

    it('asks first for an action with confirm, and runs it on Confirm', async () => {
        const pageContext = context();
        const run = jest.fn(async () => ({errors: [], successes: [9]}));
        const rowAction: ListTableRowAction = {
            id: 'trash',
            label: 'Trash',
            isDestructive: true,
            confirm: {title: 'Trash donation', body: (item) => <p>Trash #{item.id}?</p>, confirmLabel: 'Move to trash'},
            run,
        };
        const [action] = rowActionsToActions([rowAction], pageContext);
        const RenderModal = 'RenderModal' in action && action.RenderModal;
        const container = document.createElement('div');

        expect('modalHeader' in action && action.modalHeader).toBe('Trash donation');

        act(() => createRoot(container).render(<RenderModal items={[{id: 9, donor: 'Jane'}]} closeModal={jest.fn()} />));

        expect(container.textContent).toContain('Trash #9?');
        expect(run).not.toHaveBeenCalled();

        await act(async () => {
            [...container.querySelectorAll('button')].find((button) => button.textContent === 'Move to trash').click();
        });

        expect(run).toHaveBeenCalledWith({id: 9, donor: 'Jane'});
        expect(pageContext.report).toHaveBeenCalledWith({changed: 1, failed: 0}, 'Trash');
    });

    it('passes a screen modal through', () => {
        const RenderModal = () => <div />;
        const [action] = rowActionsToActions([{id: 'assign', label: 'Assign', RenderModal}], context());

        expect('RenderModal' in action && action.RenderModal).toBe(RenderModal);
    });
});
