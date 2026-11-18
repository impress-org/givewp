import {donationsListTableScreen} from './listTableScreen';

describe('donationsListTableScreen', () => {
    it('hides Payment Type by default without recurring giving', () => {
        expect(donationsListTableScreen(false)).toEqual({key: 'donations', defaults: {hiddenFields: ['paymentType']}});
    });

    it('keeps the shared defaults with recurring giving', () => {
        expect(donationsListTableScreen(true)).toEqual({key: 'donations'});
    });
});
