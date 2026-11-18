import {eventTicketFilters} from './filters';

describe('eventTicketFilters', () => {
    it('declares only search, with a hint that says text matches anywhere and an ID exactly', () => {
        expect(eventTicketFilters).toEqual([
            {
                kind: 'search',
                id: 'search',
                label: 'Search events',
                hint: 'Searches event title, description or event ID. Text matches anywhere; an ID matches exactly.',
                parameter: 'search',
            },
        ]);
    });
});
