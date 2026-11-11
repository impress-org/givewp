import {onPopState, pushEntry, readQuery, replaceEntry, SEARCH_COLLAPSE_MS} from './listTableHistory';

const PATH = '/wp-admin/edit.php';
const options = {owned: ['give-status', 'give-paged', 'give-search'], legacyKeys: []};

let now = 1_000_000;

beforeEach(() => {
    now += 60_000;
    jest.spyOn(Date, 'now').mockImplementation(() => now);
    window.history.replaceState(null, '', `${PATH}?post_type=give_forms&page=give-payment-history`);
});

afterEach(() => jest.restoreAllMocks());

describe('listTableHistory', () => {
    it('reads the address query', () => {
        expect(readQuery().get('page')).toBe('give-payment-history');
    });

    it('pushes one entry for a user change and keeps other parameters in order', () => {
        const length = window.history.length;

        pushEntry({'give-status': 'failed'}, options);

        expect(window.history.length).toBe(length + 1);
        expect(window.location.search).toBe('?post_type=give_forms&page=give-payment-history&give-status=failed');
    });

    it('replaces the entry for a correction and removes legacy names', () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&donor=12&search=smith`);
        const length = window.history.length;

        replaceEntry({'give-donor': '12'}, {owned: ['give-donor'], legacyKeys: ['donor', 'search']});

        expect(window.history.length).toBe(length);
        expect(window.location.search).toBe('?page=give-payment-history&give-donor=12');
    });

    it('replaces a search change made within 1.5 s of the previous one', () => {
        const length = window.history.length;

        pushEntry({'give-search': 's'}, options, {searchOnly: true});
        now += SEARCH_COLLAPSE_MS - 1;
        pushEntry({'give-search': 'sm'}, options, {searchOnly: true});

        expect(window.history.length).toBe(length + 1);
        expect(readQuery().get('give-search')).toBe('sm');

        now += SEARCH_COLLAPSE_MS;
        pushEntry({'give-search': 'smith'}, options, {searchOnly: true});

        expect(window.history.length).toBe(length + 2);
    });

    it('pushes a search change that follows another kind of change', () => {
        pushEntry({'give-search': 's'}, options, {searchOnly: true});
        pushEntry({'give-search': 's', 'give-paged': '2'}, options);
        const length = window.history.length;

        pushEntry({'give-search': 'sm', 'give-paged': '2'}, options, {searchOnly: true});

        expect(window.history.length).toBe(length + 1);
    });

    it('writes the search term to the address, encoded', () => {
        pushEntry({'give-search': 'a b&c@example.com'}, options, {searchOnly: true});

        expect(window.location.search).toContain('give-search=a%20b%26c%40example.com');
        expect(readQuery().get('give-search')).toBe('a b&c@example.com');
    });

    it('keeps the state of the entry as it is', () => {
        window.history.replaceState({other: 'kept'}, '', window.location.href);

        replaceEntry({'give-search': 'smith'}, options);

        expect(window.history.state).toEqual({other: 'kept'});
    });

    it('calls back on popstate until unsubscribed', () => {
        const callback = jest.fn();
        const unsubscribe = onPopState(callback);

        window.dispatchEvent(new PopStateEvent('popstate'));
        unsubscribe();
        window.dispatchEvent(new PopStateEvent('popstate'));

        expect(callback).toHaveBeenCalledTimes(1);
    });
});
