import {donationsListTableScreen} from './listTableScreen';

const fieldRoles = {primary: 'donor', description: 'amount', secondary: ['status', 'createdAt', 'campaign', 'form']};

describe('donationsListTableScreen', () => {
    it('hides Payment Type by default without recurring giving', () => {
        expect(donationsListTableScreen(false)).toEqual({
            key: 'donations',
            defaults: {hiddenFields: ['paymentType']},
            fieldRoles,
        });
    });

    it('keeps the shared defaults with recurring giving', () => {
        expect(donationsListTableScreen(true)).toEqual({key: 'donations', fieldRoles});
    });
});
