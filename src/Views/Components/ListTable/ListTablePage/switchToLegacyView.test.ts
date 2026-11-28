import type ListTableApi from '../api';
import switchToLegacyView from './switchToLegacyView';

describe('switchToLegacyView', () => {
    it('saves the legacy view choice, then calls onSwitched', async () => {
        const calls: string[] = [];
        const fetchWithArgs = jest.fn(() => {
            calls.push('fetch');
            return Promise.resolve({});
        });
        const onSwitched = jest.fn(() => calls.push('switched'));

        await switchToLegacyView({fetchWithArgs} as unknown as ListTableApi, onSwitched);

        expect(fetchWithArgs).toHaveBeenCalledWith('/view', {isLegacy: 1});
        expect(calls).toEqual(['fetch', 'switched']);
    });

    it('does not switch when saving the choice fails', async () => {
        const fetchWithArgs = jest.fn(() => Promise.reject(new Error('nope')));
        const onSwitched = jest.fn();

        await expect(switchToLegacyView({fetchWithArgs} as unknown as ListTableApi, onSwitched)).rejects.toThrow(
            'nope'
        );
        expect(onSwitched).not.toHaveBeenCalled();
    });
});
