import {applyExclusiveValues} from './exclusive';

describe('applyExclusiveValues', () => {
    it('adding an exclusive value keeps only it', () =>
        expect(applyExclusiveValues(['publish', 'pending'], ['publish', 'pending', 'trash'], ['trash'])).toEqual([
            'trash',
        ]));

    it('adding another value drops the exclusive one', () =>
        expect(applyExclusiveValues(['trash'], ['trash', 'pending'], ['trash'])).toEqual(['pending']));

    it('no exclusive values: unchanged', () => expect(applyExclusiveValues(['a'], ['a', 'b'], [])).toEqual(['a', 'b']));

    it('keeps an exclusive value that was already alone', () =>
        expect(applyExclusiveValues([], ['trash'], ['trash'])).toEqual(['trash']));
});
