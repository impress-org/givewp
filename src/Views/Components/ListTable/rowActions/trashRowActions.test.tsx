import type ListTableApi from '../api';
import {trashRowActions} from './trashRowActions';

const text = {
    restoreTitle: 'Restore',
    restoreBody: 'Restore?',
    trashTitle: 'Trash',
    trashBody: 'Trash?',
    deleteTitle: 'Delete',
    deleteBody: 'Delete?',
};

const build = () => {
    const api = {fetchWithArgs: jest.fn(async () => ({errors: [], successes: []}))};
    const actions = trashRowActions({
        api: api as unknown as ListTableApi,
        isTrashView: (parameters) => parameters.status === 'trash',
        editHref: (item) => `edit?id=${item.id}`,
        text,
    });

    return {api, byId: Object.fromEntries(actions.map((action) => [action.id, action]))};
};

describe('trashRowActions', () => {
    it('shows Edit and Trash outside the trash view, Restore and Delete in it', () => {
        const {byId} = build();
        const visibleIn = (status: string) =>
            Object.values(byId)
                .filter((action) => action.isVisible(undefined, {status}))
                .map((action) => action.id);

        expect(visibleIn('publish')).toEqual(['edit', 'trash']);
        expect(visibleIn('trash')).toEqual(['restore', 'delete']);
    });

    it('sends the selected ids to the matching endpoint', async () => {
        const {api, byId} = build();
        const items = [{id: 1}, {id: 2}];

        await byId.restore.run(items);
        await byId.trash.run(items);
        await byId.delete.run(items);

        expect(api.fetchWithArgs.mock.calls).toEqual([
            ['/untrash', {ids: '1,2'}, 'POST'],
            ['/trash', {ids: '1,2'}, 'DELETE'],
            ['/delete', {ids: '1,2'}, 'DELETE'],
        ]);
        expect(byId.edit.href({id: 7})).toBe('edit?id=7');
    });
});
