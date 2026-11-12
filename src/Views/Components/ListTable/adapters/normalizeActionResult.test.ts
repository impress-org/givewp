import {normalizeActionResult} from './normalizeActionResult';

describe('normalizeActionResult', () => {
    it('counts the successes and errors lists', () => {
        expect(normalizeActionResult({errors: [], successes: [1, 2]}, ['1', '2'])).toEqual({changed: 2, failed: 0});
        expect(normalizeActionResult({errors: [3], successes: [1]}, ['1', '3'])).toEqual({changed: 1, failed: 1});
    });

    it('counts Donation Forms delete post objects as changed', () => {
        expect(normalizeActionResult({errors: [], successes: [{ID: 1}, {ID: 2}]}, ['1', '2'])).toEqual({
            changed: 2,
            failed: 0,
        });
    });

    it('counts a Donation Forms duplicate false as failed', () => {
        expect(normalizeActionResult({errors: [], successes: [false]}, ['1'])).toEqual({changed: 0, failed: 1});
    });

    it('counts every record as failed when the request throws, with the WP_Error message', () => {
        const error = new Error(JSON.stringify({code: 'rest_forbidden', message: 'You don&#039;t have permission.'}));

        expect(normalizeActionResult(error, ['1', '2'])).toEqual({
            changed: 0,
            failed: 2,
            message: "You don't have permission.",
        });
    });

    it('reads the message of an apiFetch error object', () => {
        expect(normalizeActionResult({code: 'x', message: 'Nope'}, ['1'])).toEqual({
            changed: 0,
            failed: 1,
            message: 'Nope',
        });
    });

    it('reports nothing for an action that returns nothing', () => {
        expect(normalizeActionResult(undefined, ['1'])).toEqual({changed: 0, failed: 0});
    });
});
