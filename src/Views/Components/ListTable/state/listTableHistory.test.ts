import {
    isCurrentQuery,
    onPopState,
    pushEntry,
    readQuery,
    readTabState,
    replaceEntry,
    SEARCH_COLLAPSE_MS,
} from './listTableHistory';

const PATH = '/wp-admin/edit.php';
const options = {owned: ['give-status', 'give-paged'], legacyKeys: []};
const tabState = (search = '') => ({screen: 'donations' as const, search});

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

        pushEntry({'give-status': 'failed'}, options, tabState());

        expect(window.history.length).toBe(length + 1);
        expect(window.location.search).toBe('?post_type=give_forms&page=give-payment-history&give-status=failed');
    });

    it('replaces the entry for a correction and removes legacy names', () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&donor=12&search=smith`);
        const length = window.history.length;

        replaceEntry({'give-donor': '12'}, {owned: ['give-donor'], legacyKeys: ['donor', 'search']}, tabState());

        expect(window.history.length).toBe(length);
        expect(window.location.search).toBe('?page=give-payment-history&give-donor=12');
    });

    it('replaces a search change made within 1.5 s of the previous one', () => {
        const length = window.history.length;

        pushEntry({}, options, tabState('s'), {searchOnly: true});
        now += SEARCH_COLLAPSE_MS - 1;
        pushEntry({}, options, tabState('sm'), {searchOnly: true});

        expect(window.history.length).toBe(length + 1);
        expect(readTabState('donations')?.search).toBe('sm');

        now += SEARCH_COLLAPSE_MS;
        pushEntry({}, options, tabState('smith'), {searchOnly: true});

        expect(window.history.length).toBe(length + 2);
    });

    it('pushes a search change that follows another kind of change', () => {
        pushEntry({}, options, tabState('s'), {searchOnly: true});
        pushEntry({'give-paged': '2'}, options, tabState('s'));
        const length = window.history.length;

        pushEntry({'give-paged': '2'}, options, tabState('sm'), {searchOnly: true});

        expect(window.history.length).toBe(length + 1);
    });

    it('never writes the search term to the address', () => {
        pushEntry({}, options, tabState('smith'), {searchOnly: true});

        expect(window.location.href).not.toContain('smith');
    });

    it('writes the search to history.state and keeps unrelated keys', () => {
        window.history.replaceState({other: 'kept'}, '', window.location.href);

        replaceEntry({}, options, tabState('smith'));

        expect(window.history.state).toEqual({other: 'kept', giveListTable: {screen: 'donations', search: 'smith'}});
    });

    it("reads only this screen's tab state", () => {
        replaceEntry({}, options, tabState('smith'));

        expect(readTabState('donations')).toEqual({screen: 'donations', search: 'smith'});
        expect(readTabState('donors')).toBeUndefined();
    });

    it('tells whether the address already holds the parameters', () => {
        replaceEntry({'give-status': 'failed'}, options, tabState());

        expect(isCurrentQuery({'give-status': 'failed'}, options)).toBe(true);
        expect(isCurrentQuery({}, options)).toBe(false);
        expect(isCurrentQuery({'give-status': 'failed'}, {...options, legacyKeys: ['page']})).toBe(false);
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

describe('listTableHistory search copy', () => {
    beforeEach(() => window.sessionStorage.clear());

    it('restores the search after the admin canonical-URL script clears history.state', () => {
        pushEntry({'give-status': 'failed'}, options, tabState('smith'));
        window.history.replaceState(null, '', window.location.href);

        expect(readTabState('donations')).toEqual(tabState('smith'));
    });

    it('reads no search in a new tab with no copy', () => {
        expect(readTabState('donations')).toBeUndefined();
    });

    it('removes the copy when the search is cleared', () => {
        pushEntry({}, options, tabState('smith'));
        pushEntry({}, options, tabState(''), {searchOnly: true});

        expect(window.sessionStorage.length).toBe(0);
    });

    it('keeps the page working when storage throws', () => {
        jest.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
            throw new Error('blocked');
        });
        jest.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
            throw new Error('blocked');
        });

        expect(() => pushEntry({}, options, tabState('smith'))).not.toThrow();
        window.history.replaceState(null, '', window.location.href);
        expect(readTabState('donations')).toBeUndefined();
    });

    it('does not read the copy for another address or screen', () => {
        pushEntry({'give-status': 'failed'}, options, tabState('smith'));
        window.history.replaceState(null, '', `${PATH}?post_type=give_forms&page=give-payment-history`);

        expect(readTabState('donations')).toBeUndefined();

        pushEntry({'give-status': 'failed'}, options, tabState('smith'));
        window.history.replaceState(null, '', window.location.href);

        expect(readTabState('donors')).toBeUndefined();
    });
});
