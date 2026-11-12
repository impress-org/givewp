import {runListAction} from './runListAction';

const context = () => ({report: jest.fn(), refresh: jest.fn(async () => undefined), clearSelection: jest.fn()});

describe('runListAction', () => {
    it('reports the counted reply, clears the selection and reloads when nothing failed', async () => {
        const pageContext = context();
        const onDone = jest.fn();

        await runListAction(
            {run: async () => ({errors: [], successes: [1, 2]}), ids: [1, 2], label: 'Trash', onDone},
            pageContext
        );

        expect(onDone).toHaveBeenCalled();
        expect(pageContext.report).toHaveBeenCalledWith({changed: 2, failed: 0}, 'Trash');
        expect(pageContext.clearSelection).toHaveBeenCalled();
        expect(pageContext.refresh).toHaveBeenCalled();
    });

    it('keeps the selection when a record failed, and counts a thrown error as a failure', async () => {
        const pageContext = context();

        await runListAction(
            {
                run: async () => {
                    throw new Error('Nope');
                },
                ids: [1],
                label: 'Delete',
            },
            pageContext
        );

        expect(pageContext.report).toHaveBeenCalledWith({changed: 0, failed: 1, message: 'Nope'}, 'Delete');
        expect(pageContext.clearSelection).not.toHaveBeenCalled();
        expect(pageContext.refresh).toHaveBeenCalled();
    });
});
