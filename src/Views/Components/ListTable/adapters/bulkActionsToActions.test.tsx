import {act} from 'react';
import {createRoot} from 'react-dom/client';
import type {BulkActionsConfig} from '../ListTablePage/types';
import {bulkActionsToActions, BulkActionContext} from './bulkActionsToActions';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const context = (overrides: Partial<BulkActionContext> = {}): BulkActionContext => ({
    data: {items: [], totalItems: 0, totalPages: 0, defaultForm: 2},
    parameters: {status: ['trash']},
    report: jest.fn(),
    refresh: jest.fn(async () => undefined),
    clearSelection: jest.fn(),
    openCustomAction: jest.fn(),
    ...overrides,
});

const trash: BulkActionsConfig = {
    label: 'Trash',
    value: 'trash',
    type: 'danger',
    isIdSelectable: (id, data) => data.defaultForm !== Number(id),
    action: jest.fn(async () => ({errors: 0, successes: 2})),
    confirm: (selected, names) => <p>Trash {names.join(', ')}?</p>,
};

describe('bulkActionsToActions', () => {
    it('leaves out an action whose isVisible rule is false for the data and parameters', () => {
        const isVisible = jest.fn((data, parameters) => !parameters.status.includes('trash'));
        const actions = bulkActionsToActions([{...trash, isVisible}], context());

        expect(actions).toEqual([]);
        expect(isVisible).toHaveBeenCalledWith(context().data, {status: ['trash']});
    });

    it('marks every action as a bulk action, danger as destructive, and maps isIdSelectable to isEligible', () => {
        const [action] = bulkActionsToActions([trash], context());

        expect(action).toMatchObject({id: 'bulk-trash', label: 'Trash', supportsBulk: true, isDestructive: true});
        expect(action.isEligible({id: 2})).toBe(false);
        expect(action.isEligible({id: 3})).toBe(true);
    });

    it('opens a custom action through the page instead of a DataViews modal', () => {
        const merge: BulkActionsConfig = {label: 'Merge', value: 'merge', type: 'custom', confirm: () => null};
        const pageContext = context();
        const [action] = bulkActionsToActions([merge], pageContext);

        expect('RenderModal' in action).toBe(false);
        'callback' in action && action.callback([{id: 1}, {id: 2}], {registry: null});
        expect(pageContext.openCustomAction).toHaveBeenCalledWith(merge, [{id: 1}, {id: 2}]);
    });

    it('accepts add-on actions in the Give Recurring shape and a null core list', () => {
        const addon = {
            label: 'Cancel',
            value: 'cancel',
            action: async () => ({errors: [], successes: []}),
            confirm: () => 'Sure?',
        };

        expect(() =>
            bulkActionsToActions([...([] as BulkActionsConfig[]), addon as BulkActionsConfig], context())
        ).not.toThrow();
    });

    it('runs the action on confirm, then reports, clears the selection and refreshes', async () => {
        const pageContext = context();
        const [action] = bulkActionsToActions([trash], pageContext);
        const closeModal = jest.fn();
        const container = document.createElement('div');
        const RenderModal = 'RenderModal' in action && action.RenderModal;

        act(() =>
            createRoot(container).render(
                <RenderModal
                    items={[
                        {id: 4, title: 'Spring'},
                        {id: 5, title: 'Fall'},
                    ]}
                    closeModal={closeModal}
                />
            )
        );

        expect(container.textContent).toContain('Trash Spring, Fall?');

        await act(async () => {
            [...container.querySelectorAll('button')].find((button) => button.textContent === 'Confirm').click();
        });

        expect(trash.type !== 'custom' && trash.action).toHaveBeenCalledWith(['4', '5']);
        expect(closeModal).toHaveBeenCalled();
        expect(pageContext.report).toHaveBeenCalledWith({changed: 2, failed: 0}, 'Trash');
        expect(pageContext.clearSelection).toHaveBeenCalled();
        expect(pageContext.refresh).toHaveBeenCalled();
    });
});
